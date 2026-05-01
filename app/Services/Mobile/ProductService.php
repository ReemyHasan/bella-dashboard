<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\ImportantProduct;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\SubTeam;
use App\Models\Team;

class ProductService
{
    public function list($request)
    {
        $user = auth()->user();

        return Product::with([
            'mainCategory',
            'subCategory',
            'mainImage',
            'zonePrices.zone.currency',
            'brand',
            'tags',
        ])->filterBy($request->all())->where('active', true)
            ->withExists([
                'importantScopes as is_important' => fn($q) => $this->importantScope($q, $user)
            ])
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function show(Product $product)
    {
        $user = auth()->user();

        return Product::with([
            'images',
            'tags',
            'zonePrices.zone.currency',
            'mainCategory',
            'subCategory',
            'mainImage',
            'brand'
        ])
            ->withExists([
                'importantScopes as is_important' => fn($q) => $this->importantScope($q, $user)

            ])
            ->findOrFail($product->id);
    }

    public function selectAvailable($search = null)
    {

        $products = Product::query()->when(!is_null($search), function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('slug', 'like', "%$search%");
            });
        })->orderBy('id')->where('active', true)->get([
            'id',
            'name',
            'slug',
            'active',
        ]);

        return $products;
    }

    public function getImportantProducts()
    {
        $user = auth()->user();
        return Product::whereHas('importantScopes', function ($q) use ($user) {

            $q->where(function ($query) use ($user) {

                // Subteam priority
                if ($user->subteam_id) {
                    $query->orWhere(function ($q2) use ($user) {
                        $q2->where('important_for_type', SubTeam::class)
                            ->where('important_for_id', $user->subteam_id);
                    });
                }
                if ($user->team_id) {
                    $query->orWhere(function ($q2) use ($user) {
                        $q2->where('important_for_type', Team::class)
                            ->where('important_for_id', $user->team_id);
                    });
                }
            });
        })->get();
    }

    public function markAsImportant(Product $product)
    {
        $user = auth()->user();

        if ($user->hasRole('Team Manager')) {

            $team = $user->managedTeam;

            if (!$team) {
                throw new CustomException('لا يوجد فريق مرتبط');
            }

            ImportantProduct::updateOrCreate([
                'product_id' => $product->id,
                'important_for_type' => Team::class,
                'important_for_id' => $team->id,
            ], [
                'created_by_type' => AppUser::class,
                'created_by_id' => $user->id,
            ]);
        }

        // SubTeam Leader
        elseif ($user->hasRole('Team Leader')) {

            $subTeam = $user->ledSubTeam;

            if (!$subTeam) {
                throw new CustomException('لا يوجد فريق فرعي مرتبط');
            }

            ImportantProduct::updateOrCreate([
                'product_id' => $product->id,
                'important_for_type' => SubTeam::class,
                'important_for_id' => $subTeam->id,
            ], [
                'created_by_type' => AppUser::class,
                'created_by_id' => $user->id,
            ]);
        } else {
            throw new CustomException('غير مصرح لك');
        }
    }

    private function importantScope($query, $user)
    {
        $query->where(function ($q) use ($user) {

            if ($user->subteam_id) {
                $q->orWhere(function ($q2) use ($user) {
                    $q2->where('important_for_type', SubTeam::class)
                        ->where('important_for_id', $user->subteam_id);
                });
            }

            if ($user->team_id) {
                $q->orWhere(function ($q2) use ($user) {
                    $q2->where('important_for_type', Team::class)
                        ->where('important_for_id', $user->team_id);
                });
            }
        });
    }
}
