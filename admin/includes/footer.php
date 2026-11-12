<?php
declare(strict_types=1);

/**
 * Waypure Admin Shared Footer
 * Pure Core PHP 8.4+ Architecture
 */
?>
  </div> <!-- /.admin-wrapper -->

  <!-- Bootstrap 5.3 Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Sidebar Toggle Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const toggle = document.getElementById('sidebar-toggle');
      const sidebar = document.getElementById('admin-sidebar');
      if (toggle && sidebar) {
        toggle.addEventListener('click', function() {
          sidebar.classList.toggle('show');
        });
      }

      // Auto dismiss flash alerts after 5 seconds
      const alerts = document.querySelectorAll('.alert-dismissible');
      alerts.forEach(function(alert) {
        setTimeout(function() {
          const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
          if (bsAlert) bsAlert.close();
        }, 5000);
      });
    });
  </script>
</body>
</html>

