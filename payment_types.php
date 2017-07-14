<?php
class PiryxContributionTypes {
    public static function single_or_recurring() {
        ?>
        <div class="section" id="contribution-type">
    <h2>Contribution Type</h2>
    <input type="hidden" value="Monthly" name="RecurringPeriod" id="RecurringPeriod">
    <ul>
    <li><label><input type="radio" value="False" name="IsRecurring" id="IsRecurring"> I want to donate the above amount a single time.</label></li>
    <li><label><input type="radio" value="True" name="IsRecurring" id="IsRecurring"> I want to donate the above amount today and continue donating monthly for</label> <label><select name="NumberOfRecurringMonths" id="NumberOfRecurringMonths" gtbfieldid="94"><option value="0">every month until cancelled</option>
<option value="1">1 additional months</option>
<option value="2">2 additional months</option>
<option value="3">3 additional months</option>
<option value="4">4 additional months</option>
<option value="5">5 additional months</option>
<option value="6">6 additional months</option>
<option value="7">7 additional months</option>
<option value="8">8 additional months</option>
<option value="9">9 additional months</option>
<option value="10">10 additional months</option>
<option value="11">11 additional months</option>
</select> starting <?php echo date('F j', strtotime('+1 month')); ?>.</label></li>
    </ul>
</div>
        <?php
    } //end single_or_recurring
    public static function recurring_only() {
        ?>
        <div class="section" id="contribution-type">
    <input type="hidden" value="True" name="IsRecurring" id="IsRecurring">
    <input type="hidden" value="Monthly" name="RecurringPeriod" id="RecurringPeriod">
    <p>I want to donate the above amount today and continue donating monthly for <label><select name="NumberOfRecurringMonths" id="NumberOfRecurringMonths" gtbfieldid="164"><option value="0">every month until cancelled</option>
<option value="1">1 additional months</option>
<option value="2">2 additional months</option>
<option value="3">3 additional months</option>
<option value="4">4 additional months</option>
<option value="5">5 additional months</option>
<option value="6">6 additional months</option>
<option value="7">7 additional months</option>
<option value="8">8 additional months</option>
<option value="9">9 additional months</option>
<option value="10">10 additional months</option>
<option value="11">11 additional months</option>
</select> starting <?php echo date('F j', strtotime('+1 month')); ?>.</label></p>
</div>
        <?php
    }
    public static function yearly_subscription() {
     ?>
     <div class="section" id="contribution-type">
    <h2>Contribution Type</h2>
    <input type="hidden" value="True" name="IsRecurring" id="IsRecurring">
    <input type="hidden" value="0" name="NumberOfRecurringMonths" id="NumberOfRecurringMonths">
    <p id="annual-subscription-option"><label><input type="radio" value="Annually" name="RecurringPeriod" id="RecurringPeriod-Annually"> I would like to make one (1) payment per year of <span id="annual-amount">$0</span></label></p>
    <p id="semiannual-subscription-option"><label><input type="radio" value="SemiAnnually" name="RecurringPeriod" id="RecurringPeriod-SemiAnnually"> I would like to make two (2) payments per year of <span id="semiannual-amount">$0</span> on <?php echo date('F j'); ?> and <?php echo date('F j', strtotime('+6 months') ); ?></label></p>
    <p id="quarterly-subscription-option"><label><input type="radio" value="Quarterly" name="RecurringPeriod" id="RecurringPeriod-Quarterly"> I would like to make four (4) payments per year of <span id="quarterly-amount">$0</span> on <?php echo date('F j'); ?>, <?php echo date('F j', strtotime('+3 months') ); ?>, <?php echo date('F j', strtotime('+3 months') ); ?> and <?php echo date('F j', strtotime('+3 months') ); ?></label></p>
    <p>All payments will occur on the specified dates each year until cancelled.</p>
</div>
     <?php
    } //end subscription
    public static function one_time() {
        ?>
        <input type="hidden" value="False" name="IsRecurring" id="IsRecurring">
        <input type="hidden" value="" name="NumberOfRecurringMonths" id="NumberOfRecurringMonths">
        <?php
    } //end one_time
}