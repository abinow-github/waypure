<?php
declare(strict_types=1);

/**
 * Waypure WhatsApp Inquiries & Orders Management
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$pdo = getDb();
$pageTitle = 'WhatsApp Orders & Inquiries | Waypure Admin';
$activeNav = 'enquiries';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'update_status') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'enquiries.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $status = trim((string)($_POST['status'] ?? 'new'));
    $notes = trim((string)($_POST['admin_notes'] ?? ''));

    if (in_array($status, ['new', 'contacted', 'completed', 'cancelled'], true) && $id > 0) {
        $upd = $pdo->prepare('UPDATE `enquiries` SET status = :status, admin_notes = :notes WHERE id = :id');
        $upd->execute([':status' => $status, ':notes' => $notes ?: null, ':id' => $id]);
        setFlash('success', 'Inquiry status updated successfully.');
    }

    header('Location: ' . ADMIN_URL . 'enquiries.php');
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'delete') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid security token.');
        header('Location: ' . ADMIN_URL . 'enquiries.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $del = $pdo->prepare('DELETE FROM `enquiries` WHERE id = :id');
    $del->execute([':id' => $id]);
    setFlash('success', 'Inquiry deleted.');

    header('Location: ' . ADMIN_URL . 'enquiries.php');
    exit;
}

// Filters
$filterStatus = trim((string)($_GET['status'] ?? ''));
$filterType = trim((string)($_GET['type'] ?? ''));
$search = trim((string)($_GET['search'] ?? ''));
$highlightId = !empty($_GET['highlight']) ? (int)$_GET['highlight'] : 0;

$query = 'SELECT * FROM `enquiries` WHERE 1=1';
$params = [];

if (!empty($filterStatus) && in_array($filterStatus, ['new', 'contacted', 'completed', 'cancelled'], true)) {
    $query .= ' AND status = :st';
    $params[':st'] = $filterStatus;
}

if (!empty($filterType) && in_array($filterType, ['product_enquiry', 'whatsapp_order', 'general'], true)) {
    $query .= ' AND type = :type';
    $params[':type'] = $filterType;
}

if (!empty($search)) {
    $query .= ' AND (customer_name LIKE :s OR customer_phone LIKE :s OR enquiry_code LIKE :s OR city LIKE :s OR product_name LIKE :s)';
    $params[':s'] = '%' . $search . '%';
}

$query .= ' ORDER BY id DESC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="admin-content">
  
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color:var(--admin-ink);">WhatsApp Orders &amp; Inquiries</h1>
      <p class="text-muted small mb-0">Track and respond to customer requests initiated through the website and WhatsApp.</p>
    </div>
  </div>

  <?= renderFlash() ?>

  <!-- Filter Strip -->
  <div class="card-custom mb-4">
    <div class="p-3">
      <form method="GET" action="<?= ADMIN_URL ?>enquiries.php" class="row g-2 align-items-center">
        
        <div class="col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Search by name, phone, ref code, location..." value="<?= e($search) ?>">
          </div>
        </div>

        <div class="col-sm-6 col-md-3">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="new" <?= $filterStatus === 'new' ? 'selected' : '' ?>>New / Unanswered</option>
            <option value="contacted" <?= $filterStatus === 'contacted' ? 'selected' : '' ?>>In Discussion / Contacted</option>
            <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed / Installed</option>
            <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled / Closed</option>
          </select>
        </div>

        <div class="col-sm-6 col-md-2">
          <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="product_enquiry" <?= $filterType === 'product_enquiry' ? 'selected' : '' ?>>Product Enquiries</option>
            <option value="whatsapp_order" <?= $filterType === 'whatsapp_order' ? 'selected' : '' ?>>WhatsApp Orders</option>
            <option value="general" <?= $filterType === 'general' ? 'selected' : '' ?>>General Inquiries</option>
          </select>
        </div>

        <div class="col-sm-6 col-md-2 d-flex gap-1">
          <button type="submit" class="btn btn-sm btn-admin-primary w-100">Filter</button>
          <?php if (!empty($search) || !empty($filterStatus) || !empty($filterType)): ?>
            <a href="<?= ADMIN_URL ?>enquiries.php" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

  <!-- Inquiries Table Card -->
  <div class="card-custom">
    <div class="card-custom-header">
      <h2 class="h5 fw-bold mb-0" style="color:var(--admin-ink);">Total Inquiries: <?= count($enquiries) ?></h2>
    </div>

    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Ref &amp; Date</th>
            <th>Customer</th>
            <th>Product / Type</th>
            <th>Location</th>
            <th>Total Value</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($enquiries)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No inquiries or orders found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($enquiries as $enq): ?>
              <tr class="<?= $highlightId === (int)$enq['id'] ? 'table-warning' : '' ?>">
                <td>
                  <span class="fw-bold font-monospace small" style="color:var(--admin-ink);"><?= e($enq['enquiry_code']) ?></span>
                  <div class="text-muted" style="font-size:11px;"><?= formatDate($enq['created_at']) ?></div>
                </td>
                <td>
                  <div class="fw-bold fs-6"><?= e($enq['customer_name']) ?></div>
                  <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?= e($enq['customer_phone']) ?></div>
                  <?php if (!empty($enq['customer_email'])): ?>
                    <div class="text-muted small" style="font-size:11px;"><i class="bi bi-envelope me-1"></i><?= e($enq['customer_email']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 200px;" title="<?= e($enq['product_name'] ?? '') ?>">
                    <?= e($enq['product_name'] ?: 'General Purifier Enquiry') ?>
                  </div>
                  <span class="badge <?= $enq['type'] === 'whatsapp_order' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' ?>" style="font-size:10px;">
                    <?= $enq['type'] === 'whatsapp_order' ? '🛒 WhatsApp Order' : '💬 Product Enquiry' ?>
                  </span>
                </td>
                <td>
                  <div><?= e($enq['city']) ?></div>
                  <?php if (!empty($enq['address'])): ?>
                    <small class="text-muted d-block text-truncate" style="max-width: 160px;" title="<?= e($enq['address']) ?>"><?= e($enq['address']) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ((float)$enq['total_amount'] > 0): ?>
                    <div class="fw-bold text-dark"><?= formatPrice($enq['total_amount']) ?></div>
                    <small class="text-muted text-uppercase" style="font-size:10px;"><?= e($enq['payment_method'] ?? 'COD') ?></small>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge-status badge-<?= e($enq['status']) ?>">
                    <?= e($enq['status']) ?>
                  </span>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <?php if (!empty($enq['whatsapp_url'])): ?>
                      <a href="<?= e($enq['whatsapp_url']) ?>" target="_blank" class="btn btn-sm btn-success px-2 py-1" title="Open WhatsApp Chat">
                        <i class="bi bi-whatsapp"></i>
                      </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-admin-outline px-2 py-1" data-bs-toggle="modal" data-bs-target="#enqModal-<?= $enq['id'] ?>" title="View &amp; Update Status">
                      <i class="bi bi-eye"></i>
                    </button>
                    <form method="POST" action="<?= ADMIN_URL ?>enquiries.php?action=delete" onsubmit="return confirm('Delete this inquiry record?');" class="d-inline">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>

              <!-- View Details & Status Update Modal -->
              <div class="modal fade" id="enqModal-<?= $enq['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content border-0 shadow">
                    <form method="POST" action="<?= ADMIN_URL ?>enquiries.php?action=update_status">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                      
                      <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Inquiry Details &mdash; <?= e($enq['enquiry_code']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>

                      <div class="modal-body p-4">
                        <div class="row g-4 mb-4">
                          <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-bold small" style="letter-spacing:1px;">Customer Information</h6>
                            <div class="p-3 bg-light rounded">
                              <div class="fw-bold fs-6"><?= e($enq['customer_name']) ?></div>
                              <div class="mt-1"><i class="bi bi-telephone me-2 text-muted"></i><?= e($enq['customer_phone']) ?></div>
                              <?php if (!empty($enq['customer_email'])): ?>
                                <div class="mt-1"><i class="bi bi-envelope me-2 text-muted"></i><?= e($enq['customer_email']) ?></div>
                              <?php endif; ?>
                              <div class="mt-1"><i class="bi bi-geo-alt me-2 text-muted"></i><?= e($enq['city']) ?><?= !empty($enq['pincode']) ? ' - ' . e($enq['pincode']) : '' ?></div>
                              <?php if (!empty($enq['address'])): ?>
                                <div class="mt-2 text-muted small border-top pt-2"><strong>Address:</strong> <?= nl2br(e($enq['address'])) ?></div>
                              <?php endif; ?>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-bold small" style="letter-spacing:1px;">Inquiry Metadata</h6>
                            <div class="p-3 bg-light rounded">
                              <div><strong>Type:</strong> <?= e(strtoupper(str_replace('_', ' ', $enq['type']))) ?></div>
                              <div class="mt-1"><strong>Submitted:</strong> <?= formatDate($enq['created_at']) ?></div>
                              <div class="mt-1"><strong>Total Amount:</strong> <?= formatPrice($enq['total_amount']) ?></div>
                              <div class="mt-1"><strong>Payment Preference:</strong> <?= e(strtoupper($enq['payment_method'] ?? 'COD')) ?></div>
                              <div class="mt-2">
                                <?php if (!empty($enq['whatsapp_url'])): ?>
                                  <a href="<?= e($enq['whatsapp_url']) ?>" target="_blank" class="btn btn-sm btn-success d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-whatsapp"></i> Chat on WhatsApp
                                  </a>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Customer Message / Notes -->
                        <?php if (!empty($enq['message'])): ?>
                          <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold small" style="letter-spacing:1px;">Customer Message / Water Details</h6>
                            <div class="p-3 bg-white border rounded">
                              <?= nl2br(e($enq['message'])) ?>
                            </div>
                          </div>
                        <?php endif; ?>

                        <!-- Ordered Items Breakdown if Cart Order -->
                        <?php if (!empty($enq['items_json'])): ?>
                          <?php $items = json_decode((string)$enq['items_json'], true); ?>
                          <?php if (is_array($items) && !empty($items)): ?>
                            <div class="mb-4">
                              <h6 class="text-uppercase text-muted fw-bold small" style="letter-spacing:1px;">Ordered Items Breakdown</h6>
                              <div class="table-responsive border rounded">
                                <table class="table table-sm mb-0">
                                  <thead class="table-light">
                                    <tr>
                                      <th>Product</th>
                                      <th>Qty</th>
                                      <th>Price</th>
                                      <th class="text-end">Line Total</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php foreach ($items as $it): ?>
                                      <tr>
                                        <td>
                                          <div class="fw-bold"><?= e($it['brand'] ?? '') ?> <?= e($it['name'] ?? '') ?></div>
                                          <small class="text-muted"><?= e($it['technology'] ?? '') ?></small>
                                        </td>
                                        <td><?= $it['quantity'] ?? 1 ?></td>
                                        <td><?= formatPrice($it['price'] ?? 0) ?></td>
                                        <td class="text-end fw-bold"><?= formatPrice($it['subtotal'] ?? ($it['price'] ?? 0)) ?></td>
                                      </tr>
                                    <?php endforeach; ?>
                                  </tbody>
                                </table>
                              </div>
                            </div>
                          <?php endif; ?>
                        <?php endif; ?>

                        <!-- Status Update & Admin Internal Notes -->
                        <div class="border-top pt-3">
                          <h6 class="text-uppercase text-muted fw-bold small mb-3" style="letter-spacing:1px;">Administrative Processing</h6>
                          <div class="row g-3">
                            <div class="col-md-5">
                              <label class="form-label small fw-bold">Update Status</label>
                              <select name="status" class="form-select">
                                <option value="new" <?= $enq['status'] === 'new' ? 'selected' : '' ?>>New / Unanswered</option>
                                <option value="contacted" <?= $enq['status'] === 'contacted' ? 'selected' : '' ?>>In Discussion / Contacted</option>
                                <option value="completed" <?= $enq['status'] === 'completed' ? 'selected' : '' ?>>Completed / Delivered / Installed</option>
                                <option value="cancelled" <?= $enq['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled / Closed</option>
                              </select>
                            </div>

                            <div class="col-md-7">
                              <label class="form-label small fw-bold">Internal Technician / Staff Notes</label>
                              <textarea name="admin_notes" class="form-control" rows="2" placeholder="e.g. Technician Rajesh assigned, installation scheduled for tomorrow 2 PM..."><?= e($enq['admin_notes'] ?? '') ?></textarea>
                            </div>
                          </div>
                        </div>

                      </div>

                      <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-admin-primary">Save Status &amp; Notes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

