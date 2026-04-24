<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0">
    <div class="position-sticky pt-3">
        <h5 class="text-center mb-4">Traffic Admin</h5>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'dashboard') ? 'active' : '' ?>" href="/dashboard">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'analytics') ? 'active' : '' ?>" href="/analytics">
                    <i class="bi bi-graph-up me-2"></i> Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'users' || strpos(uri_string(), 'users/') === 0) ? 'active' : '' ?>" href="/users">
                    <i class="bi bi-people me-2"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'about') ? 'active' : '' ?>" href="/about">
                    <i class="bi bi-info-circle me-2"></i> About System
                </a>
            </li>
            <li class="nav-item mt-5">
                <a class="nav-link text-danger" href="/logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
