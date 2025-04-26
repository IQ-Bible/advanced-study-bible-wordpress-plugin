<!-- Reading Plans -->

<?php // Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} ?>
 
<h2><?php esc_html_e('Reading Plans', 'iqbible'); ?></h2>
<div id="iqbible-reading-plan-form-container">
    <form id="iqbible-reading-plan-form" class="iqbible-reading-plan-form" method="POST" action="">
        <div class="iqbible-form-row">
            <div class="iqbible-form-group">
                <label for="iqbible-planName"><?php esc_html_e('Plan Name:', 'iqbible'); ?></label>
                <input type="text" id="iqbible-planName" name="iqbible-planName" placeholder="<?php esc_attr_e('Name for your plan', 'iqbible'); ?>" value="<?php echo esc_attr__('My Plan', 'iqbible'); ?>" required>
            </div>

            <div class="iqbible-form-group">
                <label for="iqbible-days"><?php esc_html_e('Duration:', 'iqbible'); ?></label>
                <select id="iqbible-days" name="days">
                    <option value="365"><?php esc_html_e('365 Days', 'iqbible'); ?></option>
                    <option value="180"><?php esc_html_e('180 Days', 'iqbible'); ?></option>
                    <option value="90"><?php esc_html_e('90 Days', 'iqbible'); ?></option>
                    <option value="30"><?php esc_html_e('30 Days', 'iqbible'); ?></option>
                    <option value="custom"><?php esc_html_e('Custom Days', 'iqbible'); ?></option>
                </select>
                <input type="number" id="iqbible-customDays" name="customDays" placeholder="<?php esc_attr_e('Enter custom days', 'iqbible'); ?>" style="display:none;" min="1">
            </div>

            <div class="iqbible-form-group">
                <label for="iqbible-startDate"><?php esc_html_e('Start Date:', 'iqbible'); ?></label>
                <input type="date" id="iqbible-startDate" name="requestedStartDate" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>
        <div class="iqbible-form-row">
            <div class="iqbible-form-group">
                <label for="iqbible-sections"><?php esc_html_e('Testaments:', 'iqbible'); ?></label>
                <select id="iqbible-sections" name="sections">
                    <option value="all"><?php esc_html_e('Old and New Testaments', 'iqbible'); ?></option>
                    <option value="ot"><?php esc_html_e('Old Testament', 'iqbible'); ?></option>
                    <option value="nt"><?php esc_html_e('New Testament', 'iqbible'); ?></option>
                </select>
            </div>

            <div class="iqbible-form-group">
                <label for="iqbible-age"><?php esc_html_e('Age:', 'iqbible'); ?></label>
                <input type="number" id="iqbible-age" name="requestedAge" min="1" value="18" required>
            </div>
        </div>
        <div class="iqbible-form-row">
            <button type="submit" class="iqbible-reading-plan-submit"><?php esc_html_e('Generate Reading Plan', 'iqbible'); ?></button>
        </div>
    </form>
</div>
<div id="iqbible-reading-plan-content"></div>