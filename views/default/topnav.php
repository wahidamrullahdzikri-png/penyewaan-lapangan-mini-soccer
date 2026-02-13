<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: linear-gradient(90deg, #1a1a1a 0%, #2d2d2d 100%); border-bottom: 3px solid #28a745;">
  <div class="container-fluid px-4">
    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center" href="<?= base_url('index.php') ?>" style="font-weight: 700;">
      <div class="bg-success bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-2" 
           style="width: 35px; height: 35px;">
        <i class="bi bi-trophy text-success" style="font-size: 1.2rem;"></i>
      </div>
      <span>Mini Soccer Rental</span>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navigation Items -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php if (isAdminLoggedIn()) : ?>
          <!-- Dashboard Link -->
          <li class="nav-item me-2">
            <a class="nav-link topnav-link" href="<?= base_url('admin/index.php') ?>">
              <i class="bi bi-speedometer2 me-1"></i>
              Dashboard
            </a>
          </li>

          <!-- Notifications (Optional - bisa diaktifkan nanti) -->
          <li class="nav-item me-2 d-none">
            <a class="nav-link topnav-link position-relative" href="#">
              <i class="bi bi-bell fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                3
              </span>
            </a>
          </li>

          <!-- User Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle topnav-link d-flex align-items-center" href="#" id="userDropdown" 
               role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="bg-success bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-2" 
                   style="width: 32px; height: 32px;">
                <i class="bi bi-person-circle text-success"></i>
              </div>
              <span class="d-none d-lg-inline"><?= $_SESSION['username'] ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 200px;">
              <li class="px-3 py-2 border-bottom">
                <small class="text-muted d-block">Signed in as</small>
                <strong><?= $_SESSION['username'] ?></strong>
                <br>
                <span class="badge bg-success mt-1"><?= $_SESSION['role'] ?></span>
              </li>
              <li><a class="dropdown-item py-2" href="#">
                <i class="bi bi-person me-2"></i>Profile
              </a></li>
              <li><a class="dropdown-item py-2" href="#">
                <i class="bi bi-gear me-2"></i>Settings
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger py-2" href="<?= base_url('logout.php') ?>">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
              </a></li>
            </ul>
          </li>
        <?php else : ?>
          <!-- Guest Navigation -->
          <li class="nav-item me-2">
            <a class="nav-link topnav-link" href="<?= base_url('booking/add.php') ?>">
              <i class="bi bi-calendar-plus me-1"></i>
              Booking Lapangan
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-success btn-sm px-3" href="<?= base_url('login.php') ?>">
              <i class="bi bi-box-arrow-in-right me-1"></i>
              Login Admin
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<style>
/* Topnav Styles */
.navbar {
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 1030;
}

.navbar-brand {
    font-size: 1.15rem;
    transition: all 0.3s ease;
}

.navbar-brand:hover {
    transform: scale(1.05);
    color: #28a745 !important;
}

.topnav-link {
    color: #b8b8b8 !important;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.topnav-link:hover {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745 !important;
}

.topnav-link i {
    font-size: 1rem;
}

/* Dropdown Styles */
.dropdown-menu {
    margin-top: 0.5rem !important;
    animation: fadeInDown 0.3s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    padding-left: 1.5rem;
}

.dropdown-item.text-danger:hover {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545 !important;
}

/* Button Styles */
.btn-success {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

/* Mobile Responsive */
@media (max-width: 991.98px) {
    .topnav-link {
        margin: 0.25rem 0;
    }
    
    .navbar-nav {
        padding: 1rem 0;
    }
}
</style>

<!-- Bootstrap Icons CDN (jika belum ada di header) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">