<h1>Payment Alert</h1>

<p><strong>Type:</strong> <?php echo e($alert->type); ?></p>
<p><strong>Severity:</strong> <?php echo e($alert->severity); ?></p>
<p><strong>Triggered:</strong> <?php echo e($alert->triggered_at); ?></p>

<pre><?php echo e(json_encode($alert->context, JSON_PRETTY_PRINT)); ?></pre>
<?php /**PATH /var/www/html/resources/views/emails/payment-alert.blade.php ENDPATH**/ ?>