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

        <!--begin::Debug Panel - ข้อมูล Login & Permissions-->
        <div class="card bg-light-info mb-5 mx-3">
            <div class="card-body p-3">
                <h6 class="card-title text-info mb-2">🔍 Debug Info</h6>

                <!-- ข้อมูล Route -->
                <?php
                $router = service('router');
                $controllerName = ltrim($router->controllerName(), '\\');
                $methodName = $router->methodName();
                ?>
                <div class="mb-2">
                    <strong>🛣️ Route:</strong><br>
                    <small>
                        Controller: <?= $controllerName ?><br>
                        Method: <?= $methodName ?><br>
                        Full Route: <?= $controllerName . '::' . $methodName ?><br>
                        Active Menu: <?= $activeMenu ?>
                    </small>
                </div>

                <!-- ข้อมูล User -->
                <div class="mb-2">
                    <strong>👤 User:</strong><br>
                    <small>
                        UID: <?= session('uid') ?? 'N/A' ?><br>
                        Name: <?= session('full_name') ?? 'N/A' ?><br>
                        Dept: <?= session('department') ?? 'N/A' ?><br>
                        User ID: <?= session('user_id') ?? 'N/A' ?>
                    </small>
                </div>

                <!-- ข้อมูล Permissions -->
                <div class="mb-2">
                    <strong>🔐 Permissions:</strong><br>
                    <small>
                        <?php
                        $userRoles = session('user_roles') ?? [];
                        $isAdmin = session('is_admin') ?? false;
                        $isApprover = session('is_approver') ?? false;
                        $isReporter = session('is_reporter') ?? false;
                        ?>
                        Roles: <?= !empty($userRoles) ? implode(', ', $userRoles) : 'None' ?><br>
                        Admin: <?= $isAdmin ? '✅' : '❌' ?><br>
                        Approver: <?= $isApprover ? '✅' : '❌' ?><br>
                        Reporter: <?= $isReporter ? '✅' : '❌' ?>
                    </small>
                </div>

                <!-- ข้อมูล Template Data -->
                <?php if (isset($current_user)): ?>
                <div class="mb-2">
                    <strong>📊 Template Data:</strong><br>
                    <small>
                        current_user: ✅ Available<br>
                        permissions: <?= isset($permissions) ? '✅ Available' : '❌ Missing' ?>
                    </small>
                </div>
                <?php endif; ?>

                <!-- Session Raw Data -->
                <div class="mb-0">
                    <strong>💾 Session:</strong><br>
                    <small style="font-family: monospace;">
                        isLoggedIn: <?= session('isLoggedIn') ? 'true' : 'false' ?><br>
                        <?php if (session('isLoggedIn')): ?>
                        All roles: <?= var_export(session('user_roles'), true) ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <!--end::Debug Panel-->

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
            <?php
                // เฉพาะ Admin และ StrategicViewer เท่านั้นที่เห็น Dashboard
                $userRoles = session('user_roles') ?? [];
                $canSeeDashboard = (in_array('Admin', $userRoles) || in_array('StrategicViewer', $userRoles) || in_array('Approver', $userRoles));

                // แสดง Dashboard menu เฉพาะกรณีที่มีสิทธิ์
                if ($canSeeDashboard):
                ?>
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
                            <?php if (in_array('Admin', $userRoles) || in_array('StrategicViewer', $userRoles)): ?>
                            <div class="menu-item">
                                <a class="menu-link <?= isMenuActive($activeMenu, 'dashboard-executive') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Executive</span>
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array('Approver', $userRoles) || in_array('Admin', $userRoles)): ?>
                                <div class="menu-item">
                                    <a class="menu-link <?= isMenuActive($activeMenu, 'dashboard-department') ? 'active' : '' ?>" href="<?= base_url('dashboard/department') ?>">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Department</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                <?php endif; ?>
            <!--end:Menu item Dashboard-->

            <!--begin:Menu item KeyResults-->
                <div data-kt-menu-trigger="click" class="menu-item <?= isMenuGroupActive($activeMenu, 'keyresult') ? 'here show' : '' ?> menu-accordion">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-outline ki-abstract-26 fs-2"></i>
                        </span>
                        <span class="menu-title">Key Results</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <!--end:Menu link-->
                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">

                        <!--begin:Menu item My Key Results-->
                        <?php if (isset($current_user) && ($current_user['is_reporter'] || $current_user['is_approver'] || $current_user['is_admin'])): ?>
                        <div class="menu-item">
                            <a class="menu-link <?= isMenuActive($activeMenu, 'keyresult-list') ? 'active' : '' ?>" href="<?= base_url('keyresult') ?>">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">My Key Results</span>
                            </a>
                        </div>
                        <?php endif; ?>

                        <!--begin:Menu item Pending Approvals (เฉพาะ Approver/Admin)-->
                        <?php if (isset($current_user) && ($current_user['is_approver'] || $current_user['is_admin'])): ?>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= base_url('progress/pending-approvals') ?>">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">รายงานรออนุมัติ</span>
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                    <!--end:Menu sub-->
                </div>
            <!--end:Menu item KeyResults-->

            <?php
                // ตรวจสอบว่าผู้ใช้มีสิทธิ์ Strategic Viewer หรือไม่
                $canViewStrategic = (isset($current_user) && ($current_user['is_admin'] || isStrategicViewer()));
            ?>

            <!--begin:Menu item Strategic (เฉพาะ Strategic Viewer หรือ Admin)-->
            <?php if ($canViewStrategic): ?>
                <div data-kt-menu-trigger="click" class="menu-item <?= isMenuGroupActive($activeMenu, 'strategic') ? 'here show' : '' ?> menu-accordion">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-outline ki-chart-simple fs-2"></i>
                        </span>
                        <span class="menu-title">Strategic Overview</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <!--end:Menu link-->
                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">
                        <!--begin:Menu item Overview-->
                        <div class="menu-item">
                            <a class="menu-link <?= isMenuActive($activeMenu, 'strategic-overview') ? 'active' : '' ?>" href="<?= base_url('strategic/overview') ?>">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">University Overview</span>
                            </a>
                        </div>
                        <!--end:Menu item Overview-->

                        <!--begin:Menu item Department Analysis (เฉพาะ Admin)-->
                        <?php if (isset($current_user['is_admin']) && $current_user['is_admin']): ?>
                        <div class="menu-item">
                            <a class="menu-link <?= isMenuActive($activeMenu, 'strategic-departments') ? 'active' : '' ?>" href="<?= base_url('strategic/departments') ?>">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Department Analysis</span>
                            </a>
                        </div>
                        <?php endif; ?>
                        <!--end:Menu item Department Analysis-->

                        <!--begin:Menu item Reports-->
                        <div class="menu-item">
                            <a class="menu-link <?= isMenuActive($activeMenu, 'strategic-reports') ? 'active' : '' ?>" href="<?= base_url('strategic/reports') ?>">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Strategic Reports</span>
                            </a>
                        </div>
                        <!--end:Menu item Reports-->
                    </div>
                    <!--end:Menu sub-->
                </div>
            <?php endif; ?>
            <!--end:Menu item Strategic-->


            <!--begin:Menu item Admin (เฉพาะ Admin)-->
            <?php if (isset($current_user['is_admin']) && $current_user['is_admin']): ?>
                <div data-kt-menu-trigger="click" class="menu-item <?= isMenuGroupActive($activeMenu, 'admin') ? 'here show' : '' ?> menu-accordion">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-outline ki-setting-2 fs-2"></i>
                        </span>
                        <span class="menu-title">Admin</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <!--end:Menu link-->
                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">
                        <!--begin:Menu item Permissions-->
                        <div class="menu-item">
                            <a class="menu-link <?= isMenuActive($activeMenu, 'admin-permissions') ? 'active' : '' ?>" href="<?= base_url('admin/manage-permissions') ?>">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Manage Permissions</span>
                            </a>
                        </div>
                        <!--end:Menu item Permissions-->
                    </div>
                    <!--end:Menu sub-->
                </div>
            <?php endif; ?>
            <!--end:Menu item Admin-->

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
                        <a class="menu-link" href="#" target="_blank" title="Check out the complete documentation" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Documentation</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item Logout-->
                    <div class="menu-item">
                        <a class="menu-link text-danger" href="<?= base_url('auth/logout') ?>">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Logout</span>
                        </a>
                    </div>
                    <!--end:Menu item Logout-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item Help-->
        </div>
        <!--end::Sidebar menu-->