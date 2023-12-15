<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
<!-- font awesome -->
<script src="https://kit.fontawesome.com/bf01bc6a3e.js" crossorigin="anonymous"></script>

<header>
        <div class="d-flex flex-column flex-shrink-0 sidebar-wrap">
            <a href="home.php#" class="text-decoration-none logo-wrap">
            <div class="icon-wrap"><i class="fa-solid fa-database"></i></div> <span style="white-space: nowrap;"></span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="masalah1.php" class="nav-link " aria-current="page">
                        <div class="icon-wrap">
                        <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <span style="white-space: nowrap;">Orders</span>
                    </a>
                </li>
                <li>
                    <a href="masalah2.php" class="nav-link ">
                        <div class="icon-wrap">
                        <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <span style="white-space: nowrap;">Shipping</span>
                    </a>
                </li>
                <li>
                    <a href="masalah3.php" class="nav-link ">
                        <div class="icon-wrap">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <span style="white-space: nowrap;">Refunds</span>
                    </a>
                </li>
            </ul>
        </div>
    </header>

<style>
.sidebar-wrap {
  width: 60px;
  height: 100vh;
  background-color: #725C3F;
  color: #fff;
  padding: 10px;
  transition: 0.8s;
  position: sticky;
  top: 0;
}
.sidebar-wrap:hover {
  width: 280px;
}
.sidebar-wrap:hover .logo-wrap span {
  display: flex;
}
.sidebar-wrap:hover .nav li .nav-link span {
  display: flex;
}
.sidebar-wrap:hover .dropdown-wrap strong {
  display: flex;
}
.sidebar-wrap:hover .dropdown-wrap::after {
  display: inline-block;
}
.sidebar-wrap:hover .dropdown-wrap {
  justify-content: flex-start;
}
.sidebar-wrap .logo-wrap {
  color: #fff;
  font-size: 35px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.sidebar-wrap .logo-wrap span {
  font-size: 18px;
}
.sidebar-wrap .logo-wrap .icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 40px;
  min-width: 40px;
}
.sidebar-wrap .nav {
  height: 100%;
  overflow-x: hidden;
  overflow-y: auto;
  flex-wrap: nowrap;
}
.sidebar-wrap .nav::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  border-radius: 5px;
  background-color: #f5f5f5;
}
.sidebar-wrap .nav::-webkit-scrollbar {
  width: 5px;
  background-color: #f5f5f5;
  border-radius: 5px;
}
.sidebar-wrap .nav::-webkit-scrollbar-thumb {
  border-radius: 5px;
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: #9b9b9b;
}
.sidebar-wrap .nav li {
  margin-top: 5px;
}
.sidebar-wrap .nav li .nav-link {
  color: #fff;
  padding: 0;
  font-size: 20px;
  display: flex;
  align-items: center;
  gap: 5px;
}
.sidebar-wrap .nav li .nav-link .icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 40px;
  min-width: 40px;
}
.sidebar-wrap .nav li .nav-link span {
  font-size: 16px;
}
.sidebar-wrap .nav li .nav-link.active {
  background-color: #ffa200;
}
.sidebar-wrap .nav li .nav-link:hover {
  background-color: rgba(255, 162, 0, 0.5);
}
.sidebar-wrap .dropdown-wrap {
  display: flex;
  align-items: center;
  color: #fff;
  gap: 15px;
  font-size: 16px;
}
.sidebar-wrap .dropdown-wrap .icon-wrap {
  min-width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
<script src='https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.min.js'></script>