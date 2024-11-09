<!-- Reading Plans -->
<h2>Reading Plans</h2>
<div id="iqbible-reading-plan-form-container">
    <form id="iqbible-reading-plan-form" class="iqbible-reading-plan-form" method="POST" action="">
        <div class="iqbible-form-row">
            <div class="iqbible-form-group">
                <label for="iqbible-planName">Plan Name:</label>
                <input type="text" id="iqbible-planName" name="iqbible-planName" placeholder="Name for your plan" value="My Plan" required>
            </div>

            <div class="iqbible-form-group">
                <label for="iqbible-days">Duration:</label>
                <select id="iqbible-days" name="days">
                    <option value="365">365 Days</option>
                    <option value="180">180 Days</option>
                    <option value="90">90 Days</option>
                    <option value="30">30 Days</option>
                    <option value="custom">Custom Days</option>
                </select>
                <input type="number" id="iqbible-customDays" name="customDays" placeholder="Enter custom days" style="display:none;" min="1">
            </div>

            <div class="iqbible-form-group">
                <label for="iqbible-startDate">Start Date:</label>
                <input type="date" id="iqbible-startDate" name="requestedStartDate" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>
        <div class="iqbible-form-row">
            <div class="iqbible-form-group">
                <label for="iqbible-sections">Testaments:</label>
                <select id="iqbible-sections" name="sections">
                    <option value="all">Old and New Testaments</option>
                    <option value="ot">Old Testament</option>
                    <option value="nt">New Testament</option>
                </select>
            </div>

            <div class="iqbible-form-group">
                <label for="iqbible-age">Age:</label>
                <input type="number" id="iqbible-age" name="requestedAge" min="1" value="18" required>
            </div>
        </div>
        <div class="iqbible-form-row">
            <button type="submit" class="iqbible-reading-plan-submit">Generate Reading Plan</button>
        </div>
    </form>
</div>
<div id="iqbible-reading-plan-content"></div>