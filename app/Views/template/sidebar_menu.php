<?php
// แทนที่เนื้อหาใน sidebar_menu.php ด้วยโค้ดนี้

// ดึงข้อมูล active menu จาก session
$activeMenu = session('menu.active') ?? '';

// Helper function สำหรับเช็ค menu group - เช็คว่า function มีอยู่แล้วหรือไม่
if (!function_exists('isMenuGroupActive')) {
    function isMenuGroupActive($activeMenu, $group) {
        return strpos($activeMenu, $group . '-') === 0;
    }
}

// Helper function สำหรับเช็ค specific menu - เช็คว่า function มีอยู่แล้วหรือไม่
if (!function_exists('isMenuActive')) {
    function isMenuActive($activeMenu, $menuCode) {
        return $activeMenu === $menuCode;
    }
}

// Debug - แสดงค่า activeMenu
echo "<!-- Debug: activeMenu = '$activeMenu' -->";
?>
        <!--begin::Sidebar menu-->
        <div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false" class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-bullet-primary">
            <!--begin::Heading-->
            <div class="menu-item mb-2">
                <div class="menu-heading text-uppercase fs-7 fw-bold">Menu</div>
                <!--begin::Separator-->
                <div class="app-sidebar-separator separator"></div>
                <!--end::Separator-->
            </div>
            <!--end::Heading-->

            <!--begin:Menu item Dashboard-->
            <div data-kt-menu-trigger="click" class="menu-item <?= isMenuGroupActive($activeMenu, 'dashboard') ? 'here show' : '' ?> menu-accordion">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-outline ki-home-2 fs-2"></i>
                    </span>
                    <span class="menu-title">Dashboards</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    <!--begin:Menu item Executive-->
                    <div class="menu-item">
                        <a class="menu-link <?= isMenuActive($activeMenu, 'dashboard-executive') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Executive</span>
                        </a>
                    </div>
                    <!--end:Menu item Executive-->

                    <!--begin:Menu item Department-->
                    <div class="menu-item">
                        <a class="menu-link <?= isMenuActive($activeMenu, 'dashboard-department') ? 'active' : '' ?>" href="<?= base_url('dashboard/department') ?>">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Department</span>
                        </a>
                    </div>
                    <!--end:Menu item Department-->

                    <!--begin:Menu item Progress-->
                    <div class="menu-item">
                        <a class="menu-link <?= isMenuActive($activeMenu, 'dashboard-progress') ? 'active' : '' ?>" href="<?= base_url('dashboard/progress') ?>">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Progress</span>
                        </a>
                    </div>
                    <!--end:Menu item Progress-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item Dashboard-->

            <!--begin:Menu item KeyResults-->
            <div data-kt-menu-trigger="click" class="menu-item <?= isMenuGroupActive($activeMenu, 'keyresult') ? 'here show' : '' ?> menu-accordion">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-outline ki-abstract-26 fs-2"></i>
                    </span>
                    <span class="menu-title">KeyResults</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    <!--begin:Menu item Lists-->
                    <div class="menu-item">
                        <a class="menu-link <?= isMenuActive($activeMenu, 'keyresult-list') ? 'active' : '' ?>" href="<?= base_url('keyresult') ?>">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Lists</span>
                        </a>
                    </div>
                    <!--end:Menu item Lists-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item KeyResults-->

            <!--begin:Menu item Help-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-outline ki-briefcase fs-2"></i>
                    </span>
                    <span class="menu-title">Help</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="https://preview.keenthemes.com/html/metronic/docs" target="_blank" title="Check out the complete documentation" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Documentation</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item Help-->
        </div>
        <!--end::Sidebar menu-->