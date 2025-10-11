<?php if (!defined('ABSPATH')) { exit; } ?>

<div class="wrap">
    <h1><?php echo esc_html__('WP Retainly Dashboard', 'wp-retainly'); ?></h1>
    
    <div class="wp-retainly-stats">
        <div class="stat-box">
            <h3><?php echo esc_html__('High Risk Customers', 'wp-retainly'); ?></h3>
            <p class="stat-value"><?php echo count($high_risk_customers); ?></p>
        </div>
    </div>
    
    <h2><?php echo esc_html__('High Churn Risk Customers', 'wp-retainly'); ?></h2>
    
    <?php if (empty($high_risk_customers)) : ?>
        <p><?php echo esc_html__('No high-risk customers found.', 'wp-retainly'); ?></p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Customer', 'wp-retainly'); ?></th>
                    <th><?php echo esc_html__('Email', 'wp-retainly'); ?></th>
                    <th><?php echo esc_html__('Risk Score', 'wp-retainly'); ?></th>
                    <th><?php echo esc_html__('Risk Factors', 'wp-retainly'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($high_risk_customers as $customer) : ?>
                    <?php
                    $risk = get_user_meta($customer->ID, '_retainly_churn_risk', true);
                    $factors = get_user_meta($customer->ID, '_retainly_risk_factors', true);
                    ?>
                    <tr>
                        <td><?php echo esc_html($customer->display_name); ?></td>
                        <td><?php echo esc_html($customer->user_email); ?></td>
                        <td><?php echo esc_html(round($risk * 100)) . '%'; ?></td>
                        <td><?php echo esc_html(is_array($factors) ? implode(', ', $factors) : ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
