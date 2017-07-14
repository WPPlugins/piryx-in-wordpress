function updatePaymentForm()
{
    var value = jQuery("input[name=payment]:checked").val();
    if (value != "ECheck")
    {
        jQuery("#echeck-info").hide();
        jQuery("#cc-info").show();
    } else
    {
        jQuery("#cc-info").hide();
        jQuery("#echeck-info").show();
    }
}

function popUpCscInfo()
{
    window.open(piryx_donate.csc_url,'name','height=460,width=460');
    
    return false;
}        

function popUpSecurityNotice()
{
    window.open('/donate/security','name','height=270,width=460');
    
    return false;
}

function fillBillingInfo()
{
    setValue("#BillingCountryCode", "#CountryCode");
    setValue("#billingAddress1", "#address1");
    setValue("#billingAddress2", "#address2");
    setValue("#billingCity", "#city");
    setValue("#billingState", "#state");
    setValue("#billingZip", "#zip");
    
    jQuery("#BillingCountryCode").change();
}

function setValue(id1, id2)
{
    jQuery(id1).val(jQuery(id2).val());
}

function getSelectedAmount()
{
    var amountVal = jQuery("input[name=amount]:checked").val();
    
    if (amountVal == "custom")
    {
        var customAmountVal = jQuery("#customAmount").val();
        var customAmount =  parseFloat(customAmountVal);
        return isNaN(customAmount) ? 0 : customAmount;
    }
    
    var amount = parseFloat(amountVal)
    return isNaN(amount) ? 0 : amount;
}

function updateSubscriptionAmount()
{
    var amount = getSelectedAmount();
        
    jQuery(".selected-amount").text("$" + amount);
    jQuery("#annual-amount").text("$" + amount);
    jQuery("#semiannual-amount").text(createAmountString(amount / 2));
    jQuery("#quarterly-amount").text(createAmountString(amount / 4));
    jQuery("#monthly-amount").text(createAmountString(amount / 12));
}

function createAmountString(amount)
{
    amount = (amount * 100).toFixed(0);
    
    return "$" + (amount / 100).toFixed(2);
    
}

var twitter = {
    IncreaseCount : function(DonationpageUrl) {
        jQuery.ajax({ 
            url: "/twitter/tweet", 
            type: "POST", 
            data: { "DonationPageUrl": DonationpageUrl },
            success: function(statData) {
                if (statData == 1) 
                {
                    var count = parseInt(jQuery('#tweetcount').text());
                    jQuery('#tweetcount').text(count + 1);
                }
            }
        });
    },
    
    Share : function(url) 
    { 
        window.open(url, '_blank', 'toolbar=0, status=0, width=796, height=436'); 
    } 
};


jQuery(document).ready(function() {
    jQuery("input[type=text],select").focus(function() {
        jQuery(this).addClass("focus");
    }).blur(function() {
        jQuery(this).removeClass("focus");
    });

    jQuery("#customAmount").focus(function(){
        jQuery("input[name=amount]").val(["custom"]);
    });
    jQuery("input[name=payment]").click(function() {
        updatePaymentForm();
    });
    jQuery("#billingSameAsHome").click(function() 
    {             
        if (jQuery(this).attr("checked")) 
        { 
            fillBillingInfo(); 
        }
    });
    jQuery("input[name=amount],#customAmount").click(function() {
        updateSubscriptionAmount();
    });
    jQuery("#customAmount").change(function() {
        updateSubscriptionAmount();
    });
    jQuery("#scheduled-dates .remove").click(function() {
        jQuery(this).parent().remove();
    });
    jQuery("#add-scheduled-date").click(function() {
        var content = jQuery("<div class=\"scheduled-date\"><input type=\"text\" name=\"ScheduledDates\" class=\"date\" /> <a class=\"remove\"><img src=\"/images/schedule-remove.gif\" alt=\"Remove\" /></a></div>");
        
        content.find(".remove").click(function() {
            jQuery(this).parent().remove();
        });
        
        content.find(".date").datepicker();
    });

    jQuery("#share-dialog .facebook-share-dialog a.skip").click(function() {
        jQuery("input[name=ShareOnFacebook]").removeAttr("checked");
        jQuery("#share-dialog .facebook-share-dialog").hide();
        jQuery("#share-dialog .thank-you-dialog").show();
        jQuery("#create-comment-form").submit();
        return false;
    });

    jQuery("#create-comment-form .button").click(function() {
        var shareOnFacebook = jQuery("input[name=ShareOnFacebook]").is(":checked");
        
        if (shareOnFacebook)
        {
            jQuery.fn.colorbox({ width: 440, inline: true, href: "#share-dialog", overlayClose: false, escKey: false });
            return false;
        } else {
            jQuery("#share-dialog .facebook-share-dialog").hide();
            jQuery("#share-dialog .thank-you-dialog").show();
            jQuery("#create-comment-form").submit();
            jQuery.fn.colorbox({ width: 440, inline: true, href: "#share-dialog", overlayClose: false, escKey: false });
            return false;
        }
        
        return true;
    });

    jQuery("a.connect-to-facebook").click(function() {
        var url = jQuery(this).attr("href");
        jQuery(this).attr("href", "#").attr("target", "_self");
        var w = window.open(url, "PGSConnectToFacebook", "width=1000,height=550,menubar=no,toolbar=no");
        var watchClose = setInterval(function() {
            try {
                if (w.closed) {
                    clearTimeout(watchClose);
                    jQuery("#share-dialog .facebook-share-dialog").hide();
                    jQuery("#share-dialog .thank-you-dialog").show();
                    jQuery("#create-comment-form").submit();
                }
            } catch (e) {}
        }, 200);

        return false;
    });
    
    jQuery("a.retweet").click(function() {
        var pageUrl = jQuery('#DonationPageUrl').val();
        var tweetUrl = jQuery(this).attr("href");
        twitter.Share(tweetUrl);
        twitter.IncreaseCount(pageUrl);
        return false;
    });
    
    updateSubscriptionAmount();
    updatePaymentForm();
});

