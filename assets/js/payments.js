// Payment System functionality
document.addEventListener('DOMContentLoaded', function() {
    const payButtons = document.querySelectorAll('.pay-now');
    const paymentModal = document.getElementById('paymentModal');
    const receiptButtons = document.querySelectorAll('.view-receipt');
    const receiptModal = document.getElementById('receiptModal');
    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentInfoSections = document.querySelectorAll('.payment-method-info');
    
    // Pay now buttons
    payButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.dataset.job;
            const amount = this.dataset.amount;
            
            // Set form values
            document.getElementById('paymentJobId').value = jobId;
            document.getElementById('paymentAmount').value = amount;
            
            // Update summary
            const jobCard = this.closest('.payment-card');
            const jobTitle = jobCard.querySelector('h4').textContent;
            const dueDate = jobCard.querySelector('.due-date').textContent.replace('Due: ', '');
            
            document.getElementById('summaryJobTitle').textContent = jobTitle;
            document.getElementById('summaryAmount').textContent = '$' + parseFloat(amount).toFixed(2);
            document.getElementById('summaryDueDate').textContent = dueDate;
            
            // Show modal
            paymentModal.classList.add('active');
        });
    });
    
    // View receipt buttons
    receiptButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const transactionId = this.dataset.transaction;
            
            // Mock receipt data
            const receiptData = {
                transaction_id: transactionId,
                date: '2024-01-15 14:30:00',
                job_title: 'Senior PHP Developer',
                company: 'Tech Solutions Inc.',
                amount: '$500.00',
                commission_rate: '10%',
                payment_method: 'Khalti',
                status: 'Completed'
            };
            
            const receiptContent = document.getElementById('receiptContent');
            receiptContent.innerHTML = `
                <div class="receipt">
                    <div class="receipt-header">
                        <h3>Payment Receipt</h3>
                        <span class="receipt-status status-completed">Completed</span>
                    </div>
                    
                    <div class="receipt-details">
                        <div class="detail-row">
                            <span>Transaction ID:</span>
                            <strong>${receiptData.transaction_id}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Date & Time:</span>
                            <strong>${receiptData.date}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Job Title:</span>
                            <strong>${receiptData.job_title}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Company:</span>
                            <strong>${receiptData.company}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Amount Paid:</span>
                            <strong class="amount">${receiptData.amount}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Commission Rate:</span>
                            <strong>${receiptData.commission_rate}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Payment Method:</span>
                            <strong>${receiptData.payment_method}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Status:</span>
                            <strong>${receiptData.status}</strong>
                        </div>
                    </div>
                    
                    <div class="receipt-footer">
                        <button class="btn btn-outline" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                        <button class="btn btn-primary" id="downloadReceipt">
                            <i class="fas fa-download"></i> Download PDF
                        </button>
                    </div>
                </div>
            `;
            
            // Add download functionality
            receiptContent.querySelector('#downloadReceipt').addEventListener('click', function() {
                alert('Receipt PDF download started. In production, this would generate and download a PDF.');
            });
            
            receiptModal.classList.add('active');
        });
    });
    
    // Payment method selection
    if(paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const selectedMethod = this.value;
            
            // Hide all info sections
            paymentInfoSections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected method info
            if(selectedMethod) {
                const infoSection = document.getElementById(selectedMethod + 'Info');
                if(infoSection) {
                    infoSection.style.display = 'block';
                }
            }
        });
    }
    
    // Close modals
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.classList.remove('active');
        });
    });
    
    // Close modals when clicking outside
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if(e.target === this) {
                this.classList.remove('active');
            }
        });
    });
    
    // Simulate Khalti payment
    document.addEventListener('submit', function(e) {
        if(e.target.closest('.modal-form') && e.submitter.name === 'process_payment') {
            e.preventDefault();
            
            const paymentMethod = document.getElementById('payment_method').value;
            const submitBtn = e.submitter;
            const originalText = submitBtn.innerHTML;
            
            if(!paymentMethod) {
                alert('Please select a payment method');
                return;
            }
            
            // Show processing
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                if(paymentMethod === 'khalti' || paymentMethod === 'esewa') {
                    // Redirect to payment gateway simulation
                    const confirmPayment = confirm(`You will be redirected to ${paymentMethod.toUpperCase()} payment page. Continue?`);
                    
                    if(confirmPayment) {
                        // In production, this would redirect to actual payment gateway
                        alert('Payment successful! Returning to payment history...');
                        
                        // Reload page to show updated payment
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                } else {
                    // Bank transfer
                    alert('Please complete the bank transfer using the details provided. Your payment will be verified within 24 hours.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    paymentModal.classList.remove('active');
                }
            }, 1500);
        }
    });
});