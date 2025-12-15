<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'recruiter') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Commission configuration
$commission_rate = 0.10; // 10% commission

// Get recruiter's payments
$payments_stmt = $conn->prepare("
    SELECT p.*, j.title as job_title, c.company_name 
    FROM payments p 
    JOIN jobs j ON p.job_id = j.id 
    JOIN companies c ON j.company_id = c.id 
    WHERE p.recruiter_id = ? 
    ORDER BY p.payment_date DESC
");
$payments_stmt->bind_param("i", $user_id);
$payments_stmt->execute();
$payments = $payments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get pending payments (hired jobs without payment)
$pending_stmt = $conn->prepare("
    SELECT j.*, c.company_name, 
           (j.salary_range * ?) as commission_amount,
           DATE_ADD(NOW(), INTERVAL 7 DAY) as due_date
    FROM jobs j 
    JOIN companies c ON j.company_id = c.id 
    WHERE j.recruiter_id = ? 
    AND j.id NOT IN (SELECT job_id FROM payments WHERE status = 'completed')
    AND EXISTS (
        SELECT 1 FROM applications a 
        WHERE a.job_id = j.id AND a.status = 'hired'
    )
");
$pending_stmt->bind_param("di", $commission_rate, $user_id);
$pending_stmt->execute();
$pending_payments = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Process payment
if(isset($_POST['process_payment'])) {
    $job_id = $_POST['job_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    
    // In production, integrate with Khalti/eSewa API
    // This is a simulation
    $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
    
    $payment_stmt = $conn->prepare("
        INSERT INTO payments (recruiter_id, job_id, amount, commission_rate, payment_method, transaction_id, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'completed')
    ");
    $payment_stmt->bind_param("iiddss", $user_id, $job_id, $amount, $commission_rate, $payment_method, $transaction_id);
    
    if($payment_stmt->execute()) {
        $success = "Payment processed successfully! Transaction ID: " . $transaction_id;
    } else {
        $error = "Error processing payment. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - CareerPath</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/navigation.php'; ?>
    
    <main class="dashboard-main">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-credit-card"></i> Payment Management</h1>
                <p>Manage commission payments for successful hires</p>
            </div>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="payment-notice">
                <div class="notice-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Commission Policy</h3>
                </div>
                <div class="notice-body">
                    <p>For each successful hire through our platform, a commission of <strong><?php echo ($commission_rate * 100); ?>%</strong> of the job's salary is charged.</p>
                    <ul>
                        <li>Payment is due within 7 days of hiring</li>
                        <li>Unpaid commissions will restrict new job postings</li>
                        <li>We accept payments via Khalti and eSewa</li>
                        <li>All payments are secure and encrypted</li>
                    </ul>
                </div>
            </div>
            
            <div class="payment-sections">
                <div class="payment-section">
                    <h3><i class="fas fa-clock"></i> Pending Payments</h3>
                    <?php if(empty($pending_payments)): ?>
                        <div class="no-payments">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <p>No pending payments. Great job!</p>
                        </div>
                    <?php else: ?>
                        <div class="pending-payments">
                            <?php foreach($pending_payments as $payment): ?>
                            <div class="payment-card pending">
                                <div class="payment-header">
                                    <h4><?php echo htmlspecialchars($payment['title']); ?></h4>
                                    <span class="due-date">Due: <?php echo date('M d, Y', strtotime($payment['due_date'])); ?></span>
                                </div>
                                <div class="payment-details">
                                    <div class="detail-item">
                                        <span>Company:</span>
                                        <strong><?php echo htmlspecialchars($payment['company_name']); ?></strong>
                                    </div>
                                    <div class="detail-item">
                                        <span>Salary Offered:</span>
                                        <strong><?php echo htmlspecialchars($payment['salary_range']); ?></strong>
                                    </div>
                                    <div class="detail-item">
                                        <span>Commission (<?php echo ($commission_rate * 100); ?>%):</span>
                                        <strong class="amount">$<?php echo number_format($payment['commission_amount'], 2); ?></strong>
                                    </div>
                                </div>
                                <div class="payment-actions">
                                    <button class="btn btn-primary pay-now" data-job="<?php echo $payment['id']; ?>" data-amount="<?php echo $payment['commission_amount']; ?>">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="payment-section">
                    <h3><i class="fas fa-history"></i> Payment History</h3>
                    <?php if(empty($payments)): ?>
                        <div class="no-payments">
                            <i class="fas fa-receipt fa-2x"></i>
                            <p>No payment history yet</p>
                        </div>
                    <?php else: ?>
                        <div class="payment-history">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Job Title</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($payment['job_title']); ?></td>
                                        <td class="amount">$<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td>
                                            <span class="payment-method <?php echo $payment['payment_method']; ?>">
                                                <?php echo ucfirst($payment['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $payment['status']; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-icon view-receipt" data-transaction="<?php echo $payment['transaction_id']; ?>">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="payment-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-info">
                        <?php 
                        $total_paid = array_sum(array_column(array_filter($payments, function($p) { 
                            return $p['status'] == 'completed'; 
                        }), 'amount'));
                        ?>
                        <span class="stat-number">$<?php echo number_format($total_paid, 2); ?></span>
                        <span class="stat-label">Total Paid</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <?php 
                        $total_pending = array_sum(array_column($pending_payments, 'commission_amount'));
                        ?>
                        <span class="stat-number">$<?php echo number_format($total_pending, 2); ?></span>
                        <span class="stat-label">Pending Payments</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo count(array_filter($payments, function($p) { return $p['status'] == 'completed'; })); ?></span>
                        <span class="stat-label">Completed Payments</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Process Payment</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="job_id" id="paymentJobId">
                <input type="hidden" name="amount" id="paymentAmount">
                
                <div class="payment-summary">
                    <h3>Payment Summary</h3>
                    <div class="summary-item">
                        <span>Job Title:</span>
                        <strong id="summaryJobTitle"></strong>
                    </div>
                    <div class="summary-item">
                        <span>Commission Amount:</span>
                        <strong class="amount" id="summaryAmount"></strong>
                    </div>
                    <div class="summary-item">
                        <span>Due Date:</span>
                        <strong id="summaryDueDate"></strong>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select payment method</option>
                        <option value="khalti">Khalti</option>
                        <option value="esewa">eSewa</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                
                <div class="payment-method-info" id="khaltiInfo" style="display: none;">
                    <div class="method-instructions">
                        <h4><i class="fas fa-mobile-alt"></i> Pay with Khalti</h4>
                        <p>Complete payment using your Khalti wallet</p>
                        <ol>
                            <li>Click "Process Payment" below</li>
                            <li>You will be redirected to Khalti payment page</li>
                            <li>Enter your Khalti MPIN to confirm</li>
                            <li>Return to this page after successful payment</li>
                        </ol>
                    </div>
                </div>
                
                <div class="payment-method-info" id="esewaInfo" style="display: none;">
                    <div class="method-instructions">
                        <h4><i class="fas fa-wallet"></i> Pay with eSewa</h4>
                        <p>Complete payment using your eSewa account</p>
                        <ol>
                            <li>Click "Process Payment" below</li>
                            <li>You will be redirected to eSewa payment page</li>
                            <li>Login to your eSewa account</li>
                            <li>Confirm the payment details</li>
                            <li>Return to this page after successful payment</li>
                        </ol>
                    </div>
                </div>
                
                <div class="payment-method-info" id="bankInfo" style="display: none;">
                    <div class="method-instructions">
                        <h4><i class="fas fa-university"></i> Bank Transfer</h4>
                        <p>Transfer the amount to our bank account</p>
                        <div class="bank-details">
                            <p><strong>Bank Name:</strong> Global Bank</p>
                            <p><strong>Account Name:</strong> CareerPath Solutions</p>
                            <p><strong>Account Number:</strong> 123456789012</p>
                            <p><strong>SWIFT Code:</strong> GLBLNPKA</p>
                        </div>
                        <p class="note"><strong>Note:</strong> Please include your company name as reference</p>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" name="process_payment" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Receipt Modal -->
    <div id="receiptModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Payment Receipt</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/payments.js"></script>
</body>
</html>