<?php
// 確保 session 存在
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php if (!empty($_SESSION['logged_in'])): ?>
<!-- Header Page to Navigate Between the 2 pages -->
<header style="
    width: 100%;
    background: #ffffff;
    padding: 16px 24px;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    position: sticky;
    top: 0;
    z-index: 20;
">
  <nav id="navigation-header" style="max-width: 1100px; margin: 0 auto;">
    <ul style="
        display: flex; 
        gap: 28px; 
        padding: 0;
        margin: 0;
        list-style: none;
        align-items: center;
    ">
      <li>
        <a href="user_page.php"
           style="
             text-decoration: none;
             color: #111827;
             font-size: 15px;
             padding: 6px 10px;
             border-radius: 8px;
             transition: 0.2s;
           "
           onmouseover="this.style.background='#f3f4f6'"
           onmouseout="this.style.background='transparent'">
          Drug Gene Visualisation
        </a>
      </li>

      <li>
        <a href="drug_gene_loader.php"
           style="
             text-decoration: none;
             color: #111827;
             font-size: 15px;
             padding: 6px 10px;
             border-radius: 8px;
             transition: 0.2s;
           "
           onmouseover="this.style.background='#f3f4f6'"
           onmouseout="this.style.background='transparent'">
          Drug Gene Relation Loader
        </a>
      </li>
    </ul>
  </nav>
</header>
<?php endif; ?>
