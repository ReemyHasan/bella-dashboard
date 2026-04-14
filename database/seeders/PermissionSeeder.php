<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            // ================== Currency ==================
            ['name' => 'view_all_currencies', 'name_ar' => 'عرض العملات', 'guard_name' => 'dash_user_guard', 'group' => 'العملة'],
            ['name' => 'delete_currency', 'name_ar' => 'حذف عملة', 'guard_name' => 'dash_user_guard', 'group' => 'العملة'],
            ['name' => 'view_currency_by_id', 'name_ar' => 'عرض عملة', 'guard_name' => 'dash_user_guard', 'group' => 'العملة'],
            ['name' => 'create_currency', 'name_ar' => 'إدخال عملة', 'guard_name' => 'dash_user_guard', 'group' => 'العملة'],
            ['name' => 'update_currency', 'name_ar' => 'تحديث عملة', 'guard_name' => 'dash_user_guard', 'group' => 'العملة'],

            // ================== Zone ==================
            ['name' => 'view_all_zones', 'name_ar' => 'عرض المناطق الجغرافية', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة الجغرافية'],
            ['name' => 'delete_zone', 'name_ar' => 'حذف منطقة جغرافية', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة الجغرافية'],
            ['name' => 'view_zone_by_id', 'name_ar' => 'عرض منطقة جغرافية', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة الجغرافية'],
            ['name' => 'create_zone', 'name_ar' => 'إدخال منطقة جغرافية', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة الجغرافية'],
            ['name' => 'update_zone', 'name_ar' => 'تحديث منطقة جغرافية', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة الجغرافية'],

            // ================== City ==================
            ['name' => 'delete_city', 'name_ar' => 'حذف مدينة', 'guard_name' => 'dash_user_guard', 'group' => 'المدينة'],
            ['name' => 'view_city_by_id', 'name_ar' => 'عرض مدينة', 'guard_name' => 'dash_user_guard', 'group' => 'المدينة'],
            ['name' => 'view_all_cities', 'name_ar' => 'عرض المدن', 'guard_name' => 'dash_user_guard', 'group' => 'المدينة'],
            ['name' => 'create_city', 'name_ar' => 'إدخال مدينة', 'guard_name' => 'dash_user_guard', 'group' => 'المدينة'],
            ['name' => 'update_city', 'name_ar' => 'تحديث مدينة', 'guard_name' => 'dash_user_guard', 'group' => 'المدينة'],

            // ================== Warehouse ==================
            ['name' => 'delete_warehouse', 'name_ar' => 'حذف مستودع', 'guard_name' => 'dash_user_guard', 'group' => 'المستودع'],
            ['name' => 'view_warehouse_by_id', 'name_ar' => 'عرض مستودع', 'guard_name' => 'dash_user_guard', 'group' => 'المستودع'],
            ['name' => 'view_all_warehouses', 'name_ar' => 'عرض المستودعات', 'guard_name' => 'dash_user_guard', 'group' => 'المستودع'],
            ['name' => 'create_warehouse', 'name_ar' => 'إدخال مستودع', 'guard_name' => 'dash_user_guard', 'group' => 'المستودع'],
            ['name' => 'update_warehouse', 'name_ar' => 'تحديث مستودع', 'guard_name' => 'dash_user_guard', 'group' => 'المستودع'],
            ['name' => 'full_view_warehouses', 'name_ar' => 'رؤية جميع المستودعات', 'guard_name' => 'dash_user_guard', 'group' => 'المستودع'],
            ['name' => 'update_warehouse_products_directly', 'name_ar' => 'تحديث مباشر على منتجات المستودع', 'guard_name' => 'dash_user_guard', 'group' => 'المستودع'],

            // ================== Create Handover Request ==================
            ['name' => 'create_handover_request', 'name_ar' => 'إضافة طلب مناقلة', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات'],
            ['name' => 'update_handover_request', 'name_ar' => 'تحديث طلب مناقلة', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات'],
            ['name' => 'delete_handover_request', 'name_ar' => 'حذف طلب مناقلة', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات'],
            ['name' => 'view_all_handover_requests', 'name_ar' => 'عرض طلبات المناقلة', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات'],
            ['name' => 'view_handover_request_by_id', 'name_ar' => 'عرض طلب مناقلة', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات'],
            ['name' => 'full_access_handover_requests', 'name_ar' => 'صلاحية التحكم بكل طلبات المناقلة', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات'],

            // ================== Dash Users ==================
            ['name' => 'delete_dash_user', 'name_ar' => 'حذف مستخدم لوحة التحكم', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم لوحة التحكم'],
            ['name' => 'view_dash_user_by_id', 'name_ar' => 'عرض مستخدم لوحة التحكم', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم لوحة التحكم'],
            ['name' => 'view_all_dash_users', 'name_ar' => 'عرض مستخدمي لوحة التحكم', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم لوحة التحكم'],
            ['name' => 'create_dash_user', 'name_ar' => 'إدخال مستخدم لوحة التحكم', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم لوحة التحكم'],
            ['name' => 'update_dash_user', 'name_ar' => 'تحديث مستخدم لوحة التحكم', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم لوحة التحكم'],
            ['name' => 'set_dash_user_password', 'name_ar' => 'تغيير كلمة مرور مستخدم لوحة التحكم', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم لوحة التحكم'],

            // ================== Region ==================
            ['name' => 'create_region', 'name_ar' => 'إدخال منطقة', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة'],
            ['name' => 'update_region', 'name_ar' => 'تحديث منطقة', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة'],
            ['name' => 'delete_region', 'name_ar' => 'حذف منطقة', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة'],
            ['name' => 'view_region_by_id', 'name_ar' => 'عرض منطقة', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة'],
            ['name' => 'view_all_regions', 'name_ar' => 'عرض المناطق', 'guard_name' => 'dash_user_guard', 'group' => 'المنطقة'],

            // ================== Product ==================
            ['name' => 'create_product', 'name_ar' => 'إدخال منتج', 'guard_name' => 'dash_user_guard', 'group' => 'المنتج'],
            ['name' => 'update_product', 'name_ar' => 'تحديث منتج', 'guard_name' => 'dash_user_guard', 'group' => 'المنتج'],
            ['name' => 'delete_product', 'name_ar' => 'حذف منتج', 'guard_name' => 'dash_user_guard', 'group' => 'المنتج'],
            ['name' => 'view_product_by_id', 'name_ar' => 'عرض منتج', 'guard_name' => 'dash_user_guard', 'group' => 'المنتج'],
            ['name' => 'view_all_products', 'name_ar' => 'عرض المنتجات', 'guard_name' => 'dash_user_guard', 'group' => 'المنتج'],

            // ================== Main Category ==================
            ['name' => 'delete_main_category', 'name_ar' => 'حذف فئة رئيسية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الرئيسية'],
            ['name' => 'view_main_category_by_id', 'name_ar' => 'عرض فئة رئيسية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الرئيسية'],
            ['name' => 'view_all_main_categories', 'name_ar' => 'عرض الفئات الرئيسية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الرئيسية'],
            ['name' => 'create_main_category', 'name_ar' => 'إدخال فئة رئيسية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الرئيسية'],
            ['name' => 'update_main_category', 'name_ar' => 'تحديث فئة رئيسية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الرئيسية'],

            // ================== Sub Category ==================
            ['name' => 'delete_sub_category', 'name_ar' => 'حذف فئة فرعية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الفرعية'],
            ['name' => 'view_sub_category_by_id', 'name_ar' => 'عرض فئة فرعية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الفرعية'],
            ['name' => 'view_all_sub_categories', 'name_ar' => 'عرض الفئات الفرعية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الفرعية'],
            ['name' => 'create_sub_category', 'name_ar' => 'إدخال فئة فرعية', 'guard_name' => 'dash_user_guard', 'group' => 'الفئة الفرعية'],
            ['name' => 'update_sub_category', 'name_ar' => 'تحديث فئة فرعية', 'guard_name' => 'dash_user_guard',  'group' => 'الفئة الفرعية'],

            // ================== Tag ==================
            ['name' => 'delete_tag', 'name_ar' => 'حذف وسم', 'guard_name' => 'dash_user_guard', 'group' => 'الوسم'],
            ['name' => 'view_tag_by_id', 'name_ar' => 'عرض وسم', 'guard_name' => 'dash_user_guard', 'group' => 'الوسم'],
            ['name' => 'view_all_tags', 'name_ar' => 'عرض الوسوم', 'guard_name' => 'dash_user_guard', 'group' => 'الوسم'],
            ['name' => 'create_tag', 'name_ar' => 'إدخال وسم', 'guard_name' => 'dash_user_guard', 'group' => 'الوسم'],
            ['name' => 'update_tag', 'name_ar' => 'تحديث وسم', 'guard_name' => 'dash_user_guard', 'group' => 'الوسم'],

            // ================== Address ==================
            ['name' => 'delete_address', 'name_ar' => 'حذف حي', 'guard_name' => 'dash_user_guard', 'group' => 'الحي'],
            ['name' => 'view_all_addresses', 'name_ar' => 'عرض الأحياء', 'guard_name' => 'dash_user_guard', 'group' => 'الحي'],
            ['name' => 'create_address', 'name_ar' => 'إدخال حي', 'guard_name' => 'dash_user_guard', 'group' => 'الحي'],
            ['name' => 'update_address', 'name_ar' => 'تعديل حي', 'guard_name' => 'dash_user_guard', 'group' => 'الحي'],
            ['name' => 'view_address_by_id', 'name_ar' => 'عرض حي', 'guard_name' => 'dash_user_guard', 'group' => 'الحي'],

            // ================== Team ==================
            ['name' => 'create_team', 'name_ar' => 'إدخال فريق رئيسي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الرئيسي'],
            ['name' => 'delete_team', 'name_ar' => 'حذف فريق رئيسي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الرئيسي'],
            ['name' => 'view_all_teams', 'name_ar' => 'عرض الفرق الرئيسية', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الرئيسي'],
            ['name' => 'view_team_by_id', 'name_ar' => 'عرض فريق رئيسي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الرئيسي'],
            ['name' => 'update_team', 'name_ar' => 'تعديل فريق رئيسي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الرئيسي'],

            // ================== SubTeam ==================
            ['name' => 'create_subteam', 'name_ar' => 'إدخال فريق فرعي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الفرعي'],
            ['name' => 'delete_subteam', 'name_ar' => 'حذف فريق فرعي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الفرعي'],
            ['name' => 'view_all_subteams', 'name_ar' => 'عرض الفرق الفرعية', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الفرعي'],
            ['name' => 'view_subteam_by_id', 'name_ar' => 'عرض فريق فرعي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الفرعي'],
            ['name' => 'update_subteam', 'name_ar' => 'تعديل فريق فرعي', 'guard_name' => 'dash_user_guard', 'group' => 'الفريق الفرعي'],


            // ================== Invoice ==================
            ['name' => 'create_invoice', 'name_ar' => 'إدخال فاتورة', 'guard_name' => 'dash_user_guard', 'group' => 'الفاتورة'],
            ['name' => 'delete_invoice', 'name_ar' => 'حذف فاتورة', 'guard_name' => 'dash_user_guard', 'group' => 'الفاتورة'],
            ['name' => 'view_all_invoices', 'name_ar' => 'عرض الفواتير', 'guard_name' => 'dash_user_guard', 'group' => 'الفاتورة'],
            ['name' => 'view_invoice_by_id', 'name_ar' => 'عرض فاتورة', 'guard_name' => 'dash_user_guard', 'group' => 'الفاتورة'],
            ['name' => 'update_invoice', 'name_ar' => 'تعديل فاتورة', 'guard_name' => 'dash_user_guard', 'group' => 'الفاتورة'],

            // ================== Message ==================
            ['name' => 'create_message', 'name_ar' => 'إدخال رسالة', 'guard_name' => 'dash_user_guard', 'group' => 'الرسالة'],
            ['name' => 'delete_message', 'name_ar' => 'حذف رسالة', 'guard_name' => 'dash_user_guard', 'group' => 'الرسالة'],
            ['name' => 'view_all_messages', 'name_ar' => 'عرض الرسائل', 'guard_name' => 'dash_user_guard', 'group' => 'الرسالة'],
            ['name' => 'view_message_by_id', 'name_ar' => 'عرض رسالة', 'guard_name' => 'dash_user_guard', 'group' => 'الرسالة'],
            ['name' => 'update_message', 'name_ar' => 'تعديل رسالة', 'guard_name' => 'dash_user_guard', 'group' => 'الرسالة'],

            // ================== Customers ==================
            ['name' => 'create_customer', 'name_ar' => 'إدخال زبون', 'guard_name' => 'dash_user_guard', 'group' => 'الزبائن'],
            ['name' => 'delete_customer', 'name_ar' => 'حذف زبون', 'guard_name' => 'dash_user_guard', 'group' => 'الزبائن'],
            ['name' => 'view_all_customers', 'name_ar' => 'عرض الزبائن', 'guard_name' => 'dash_user_guard', 'group' => 'الزبائن'],
            ['name' => 'view_customer_by_id', 'name_ar' => 'عرض زبون', 'guard_name' => 'dash_user_guard', 'group' => 'الزبائن'],
            ['name' => 'update_customer', 'name_ar' => 'تعديل زبون', 'guard_name' => 'dash_user_guard', 'group' => 'الزبائن'],

            // ================== App Users ==================
            ['name' => 'delete_app_user', 'name_ar' => 'حذف مستخدم تطبيق', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم تطبيق'],
            ['name' => 'view_app_user_by_id', 'name_ar' => 'عرض مستخدم تطبيق', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم تطبيق'],
            ['name' => 'view_all_app_users', 'name_ar' => 'عرض مستخدمي التطبيق', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم تطبيق'],
            ['name' => 'create_app_user', 'name_ar' => 'إدخال مستخدم تطبيق', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم تطبيق'],
            ['name' => 'update_app_user', 'name_ar' => 'تحديث مستخدم تطبيق', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم تطبيق'],

            // ================== Permission ==================
            ['name' => 'view_all_permissions', 'name_ar' => 'عرض الصلاحيات', 'guard_name' => 'dash_user_guard', 'group' => 'الصلاحية'],

            // ================== Role ==================
            ['name' => 'create_role', 'name_ar' => 'إدخال دور', 'guard_name' => 'dash_user_guard', 'group' => 'الدور'],
            ['name' => 'view_all_roles', 'name_ar' => 'عرض الأدوار', 'guard_name' => 'dash_user_guard', 'group' => 'الدور'],
            ['name' => 'view_role_by_id', 'name_ar' => 'عرض الدور', 'guard_name' => 'dash_user_guard', 'group' => 'الدور'],
            ['name' => 'update_role', 'name_ar' => 'تحديث الدور', 'guard_name' => 'dash_user_guard', 'group' => 'الدور'],
            ['name' => 'delete_role', 'name_ar' => 'حذف دور', 'guard_name' => 'dash_user_guard', 'group' => 'الدور'],
            ['name' => 'update_dash_user_permissions', 'name_ar' => 'تحديث صلاحيات لمستخدم', 'guard_name' => 'dash_user_guard', 'group' => 'الدور'],


            // ================== Offer ==================
            ['name' => 'create_offer', 'name_ar' => 'إدخال عرض', 'guard_name' => 'dash_user_guard', 'group' => 'العرض'],
            ['name' => 'update_offer', 'name_ar' => 'تحديث عرض', 'guard_name' => 'dash_user_guard', 'group' => 'العرض'],
            ['name' => 'delete_offer', 'name_ar' => 'حذف عرض', 'guard_name' => 'dash_user_guard', 'group' => 'العرض'],
            ['name' => 'view_offer_by_id', 'name_ar' => 'عرض عرض', 'guard_name' => 'dash_user_guard', 'group' => 'العرض'],
            ['name' => 'view_all_offers', 'name_ar' => 'عرض العروض', 'guard_name' => 'dash_user_guard', 'group' => 'العرض'],


            // ================== Vault ==================
            ['name' => 'create_vault', 'name_ar' => 'إدخال خزنة', 'guard_name' => 'dash_user_guard', 'group' => 'الخزنة'],
            ['name' => 'update_vault', 'name_ar' => 'تحديث خزنة', 'guard_name' => 'dash_user_guard', 'group' => 'الخزنة'],
            ['name' => 'delete_vault', 'name_ar' => 'حذف خزنة', 'guard_name' => 'dash_user_guard', 'group' => 'الخزنة'],
            ['name' => 'view_vault_by_id', 'name_ar' => 'عرض خزنة', 'guard_name' => 'dash_user_guard', 'group' => 'الخزنة'],
            ['name' => 'view_all_vaults', 'name_ar' => 'عرض الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'الخزنة'],



            ['name' => 'update_company_vault', 'name_ar' => 'تحديث خزنة الشركة', 'guard_name' => 'dash_user_guard', 'group' => 'الخزنة'],
            ['name' => 'update_not_company_vault', 'name_ar' => 'تحديث خزنة غير الشركة', 'guard_name' => 'dash_user_guard', 'group' => 'الخزنة'],

            // ================== User Request Types ==================
            ['name' => 'create_user_request_type', 'name_ar' => 'إدخال نوع طلب', 'guard_name' => 'dash_user_guard', 'group' => 'نوع الطلب'],
            ['name' => 'update_user_request_type', 'name_ar' => 'تحديث نوع طلب', 'guard_name' => 'dash_user_guard', 'group' => 'نوع الطلب'],
            ['name' => 'delete_user_request_type', 'name_ar' => 'حذف نوع طلب', 'guard_name' => 'dash_user_guard', 'group' => 'نوع الطلب'],
            ['name' => 'view_user_request_type_by_id', 'name_ar' => 'عرض نوع طلب', 'guard_name' => 'dash_user_guard', 'group' => 'نوع الطلب'],
            ['name' => 'view_all_user_request_types', 'name_ar' => 'عرض أنواع الطلبات', 'guard_name' => 'dash_user_guard', 'group' => 'نوع الطلب'],


            // ================== User Requests ==================
            ['name' => 'handle_user_request', 'name_ar' => 'معالجة طلب', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات المسوقين'],
            ['name' => 'view_user_request_by_id', 'name_ar' => 'عرض طلب', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات المسوقين'],
            ['name' => 'view_all_user_requests', 'name_ar' => 'عرض طلبات المسوقين', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات المسوقين'],
            ['name' => 'mark_as_read_user_request', 'name_ar' => 'مراجعة طلب', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات المسوقين'],


            // ================== Vault Transfer ==================
            ['name' => 'create_vault_transfer', 'name_ar' => 'إدخال مناقلة بين الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات بين الخزنات'],
            ['name' => 'update_vault_transfer', 'name_ar' => 'تحديث مناقلة بين الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات بين الخزنات'],
            ['name' => 'delete_vault_transfer', 'name_ar' => 'حذف مناقلة بين الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات بين الخزنات'],

            ['name' => 'view_vault_transfer_by_id', 'name_ar' => 'عرض مناقلة بين الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات بين الخزنات'],
            ['name' => 'view_all_vault_transfers', 'name_ar' => 'عرض مناقلات الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات بين الخزنات'],
            ['name' => 'handle_vault_transfer', 'name_ar' => 'تأكيد أو إلغاء مناقلة بين الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'المناقلات بين الخزنات'],

            // ================== Cash Request ==================
            ['name' => 'create_cash_request', 'name_ar' => 'إدخال طلب رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب الرصيد'],
            ['name' => 'update_cash_request', 'name_ar' => 'تحديث طلب رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب الرصيد'],
            ['name' => 'delete_cash_request', 'name_ar' => 'حذف طلب رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب الرصيد'],

            ['name' => 'view_cash_request_by_id', 'name_ar' => 'عرض طلب رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب الرصيد'],
            ['name' => 'view_all_cash_requests', 'name_ar' => 'عرض طلبات الرصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب الرصيد'],
            ['name' => 'handle_cash_request', 'name_ar' => 'تأكيد أو إلغاء طلب رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب الرصيد'],



            // ================== financial adjustment ==================
            ['name' => 'create_financial_adjustment', 'name_ar' => 'إدخال مكافأة/خصم', 'guard_name' => 'dash_user_guard', 'group' => 'المكافأة/الخصم'],
            ['name' => 'update_financial_adjustment', 'name_ar' => 'تحديث مكافأة/خصم', 'guard_name' => 'dash_user_guard', 'group' => 'المكافأة/الخصم'],
            ['name' => 'delete_financial_adjustment', 'name_ar' => 'حذف مكافأة/خصم', 'guard_name' => 'dash_user_guard', 'group' => 'المكافأة/الخصم'],

            ['name' => 'view_financial_adjustment_by_id', 'name_ar' => 'عرض مكافأة/خصم', 'guard_name' => 'dash_user_guard', 'group' => 'المكافأة/الخصم'],
            ['name' => 'view_all_financial_adjustments', 'name_ar' => 'عرض المكافآت/الخصم', 'guard_name' => 'dash_user_guard', 'group' => 'المكافأة/الخصم'],
            ['name' => 'handle_financial_adjustment', 'name_ar' => 'تأكيد أو رفض مكافأة/خصم', 'guard_name' => 'dash_user_guard', 'group' => 'المكافأة/الخصم'],


            // ================== orders ==================
            ['name' => 'create_order', 'name_ar' => 'إدخال طلب زبون', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات الزبائن'],
            ['name' => 'update_order', 'name_ar' => 'تحديث طلب زبون', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات الزبائن'],
            ['name' => 'delete_order', 'name_ar' => 'حذف طلب زبون', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات الزبائن'],

            ['name' => 'view_order_by_id', 'name_ar' => 'عرض طلب زبون', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات الزبائن'],
            ['name' => 'view_all_orders', 'name_ar' => 'عرض طلبات الزبائن', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات الزبائن'],
            ['name' => 'handle_order', 'name_ar' => 'تأكيد أو رفض طلب زبون', 'guard_name' => 'dash_user_guard', 'group' => 'طلبات الزبائن'],



            // ================== competitions ==================
            ['name' => 'create_competition', 'name_ar' => 'إدخال هدف تسويقي', 'guard_name' => 'dash_user_guard', 'group' => 'الأهداف التسويقية'],
            ['name' => 'update_competition', 'name_ar' => 'تحديث هدف تسويقي', 'guard_name' => 'dash_user_guard', 'group' => 'الأهداف التسويقية'],
            ['name' => 'delete_competition', 'name_ar' => 'حذف هدف تسويقي', 'guard_name' => 'dash_user_guard', 'group' => 'الأهداف التسويقية'],

            ['name' => 'view_competition_by_id', 'name_ar' => 'عرض هدف تسويقي', 'guard_name' => 'dash_user_guard', 'group' => 'الأهداف التسويقية'],
            ['name' => 'view_all_competitions', 'name_ar' => 'عرض الأهداف التسويقية', 'guard_name' => 'dash_user_guard', 'group' => 'الأهداف التسويقية'],
            ['name' => 'change_status_competition', 'name_ar' => 'تأكيد أو رفض هدف تسويقي', 'guard_name' => 'dash_user_guard', 'group' => 'الأهداف التسويقية'],




            // ================== Other ==================
            ['name' => 'reset_pwd_dash_user', 'name_ar' => 'إعادة تعيين كلمة مرور لمستخدم لوحة التحكم', 'guard_name' => 'dash_user_guard', 'group' => 'مستخدم لوحة التحكم'],
            ['name' => 'update_general_settings', 'name_ar' => 'تحديث الإعدادات العامة', 'guard_name' => 'dash_user_guard', 'group' => 'الإعدادات العامة'],
            ['name' => 'view_general_settings', 'name_ar' => 'عرض الإعدادات العامة', 'guard_name' => 'dash_user_guard', 'group' => 'الإعدادات العامة'],
            ['name' => 'clear_caches', 'name_ar' => 'مسح الكاشات', 'guard_name' => 'dash_user_guard', 'group' => 'الإعدادات العامة'],
            ['name' => 'view_statistics', 'name_ar' => 'عرض الإحصائيات', 'guard_name' => 'dash_user_guard', 'group' => 'الإحصائيات'],

            ['name' => 'view_sales_reports', 'name_ar' => 'عرض تقارير المبيعات', 'guard_name' => 'dash_user_guard', 'group' => 'التقارير'],
            ['name' => 'view_warehouses_reports', 'name_ar' => 'عرض تقارير المستودعات', 'guard_name' => 'dash_user_guard', 'group' => 'التقارير'],
            ['name' => 'view_teams_reports', 'name_ar' => 'عرض تقارير الفرق', 'guard_name' => 'dash_user_guard', 'group' => 'التقارير'],
            ['name' => 'view_orders_reports', 'name_ar' => 'عرض تقارير الطلبات', 'guard_name' => 'dash_user_guard', 'group' => 'التقارير'],
            ['name' => 'view_vault_reports', 'name_ar' => 'عرض تقارير الخزنات', 'guard_name' => 'dash_user_guard', 'group' => 'التقارير'],
            ['name' => 'view_users_reports', 'name_ar' => 'عرض تقارير كشف حسابات المستخدمين', 'guard_name' => 'dash_user_guard', 'group' => 'التقارير'],


            // ================== Brand ==================
            ['name' => 'delete_brand', 'name_ar' => 'حذف ماركة', 'guard_name' => 'dash_user_guard', 'group' => 'الماركة'],
            ['name' => 'view_brand_by_id', 'name_ar' => 'عرض ماركة', 'guard_name' => 'dash_user_guard', 'group' => 'الماركة'],
            ['name' => 'view_all_brands', 'name_ar' => 'عرض الماركات', 'guard_name' => 'dash_user_guard', 'group' => 'الماركة'],
            ['name' => 'create_brand', 'name_ar' => 'إدخال ماركة', 'guard_name' => 'dash_user_guard', 'group' => 'الماركة'],
            ['name' => 'update_brand', 'name_ar' => 'تحديث ماركة', 'guard_name' => 'dash_user_guard', 'group' => 'الماركة'],

             // ================== Balance Trasfer Request ==================
            ['name' => 'view_balance_transfer_request_by_id', 'name_ar' => 'عرض طلب نقل رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب نقل رصيد'],
            ['name' => 'view_all_balance_transfer_requests', 'name_ar' => 'عرض طلبات نقل رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب نقل رصيد'],
            ['name' => 'handle_balance_transfer_request', 'name_ar' => 'تأكيد أو إلغاء طلب نقل رصيد', 'guard_name' => 'dash_user_guard', 'group' => 'طلب نقل رصيد'],



        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'], // unique key
                ],
                $permission
            );
        }
    }
}
