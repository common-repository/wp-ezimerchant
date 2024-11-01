<?php

if($_SERVER["REQUEST_METHOD"] == "POST")
{
?>
	<tr>
		<td style="vertical-align: middle; padding: 6px; cursor: pointer;">
			<a href="#"><?=$_POST["name"]?></a>
			<input name="optionid[]" type="hidden" value="<?=$_POST['optionid']?>"/>
			<input name="optionsequence[]" type="hidden" value="<?=$_POST["sequence"]?>"/>
			<input name="optionname[]" type="hidden" value="<?=$_POST["name"]?>"/>
			<input name="optiontype[]" type="hidden" value="<?=$_POST["type"]?>"/>
			<input name="optiondefault[]" type="hidden" value="<?=$_POST["default"]?>"/>
			<input name="optionprice[]" type="hidden" value="<?=$_POST["pricemodifier"]?>">
			<input name="optionvalues[]" type="hidden" value="<?=$_POST["values"]?>">
		</td>
		<td style="vertical-align: top; padding: 6px; cursor: pointer;">
			<? 	if($_POST["type"] == "text") 
				{
			?>
				<input type="text" value="<?=$_POST["default"]?>"/>
			<? 
				}
				elseif($_POST["type"] == "textarea") 
				{
			?>
				<textarea><?=$_POST["default"]?></textarea>
			<? 
				}
				elseif($_POST["type"] == "select") 
				{
					parse_str($_POST["values"], $OptionValues);
					
				
			?><select>
	        <? $valueidx = 0; foreach($OptionValues["value"] as $optionValue) { ?>
				<option><?=htmlspecialchars($optionValue)?></option>
	        <? $valueidx++; } ?>  </select>
			<? 
				}
				elseif($_POST["type"] == "checkbox") 
				{
			?>
				<input type="checkbox" <? if($_POST["default"]) { ?>checked="checked"<? } ?>/>
			<? 
				}
				elseif($_POST["type"] == "file") 
				{
			?>
				<input type="file" />
			<?
				}
			?>
		</td>
		<td style="text-align: center;">
			<input type="checkbox"/>
		</td>
	</tr>
<?
	die();
}

if($_GET["view"] == "option-add")
{
	 $OptionType = "";
	 $OptionName = "";
	 $OptionDefault = false;
	 //$OptionValidation = "";
	 //$OptionValidationMessage = "";
	 $OptionValues = array("value" => Array());
}
else
{
	$OptionID = $_GET["optionid"];
	$OptionType = $_GET["optiontype"];
	$OptionName = $_GET["optionname"];
	$OptionDefault = $_GET["optiondefault"];
	$OptionPrice = $_GET["optionprice"];	
	parse_str($_GET["optionvalues"], $OptionValues);
	
	if(!isset($OptionValues["value"]))
		$OptionValues = array("value" => Array());
}

	?>
<div id="ProductOptionEdit">
<script>
var Type = "text";
var Sequence = 1;

$("#ProductOptionEdit #OptionName").focus();

$("#ProductOptionEdit .Tabs").verttabs({
    defaulttab : { "text" : 0, "textarea" : 1, "select" : 2, "checkbox" : 3, "file" : 4}["<?=$OptionType?>"],
    ontabshow : function(tab, index)
    {
        Type = { 0 : "text", 1 : "textarea", 2 : "select", 3 : "checkbox", 4 : "file"}[index];               
    }
});

/*
$("#ProductOptionSelect").sortable({
    items : "TBODY > TR",
    opacity : 0.6,
    placeholder : "ui-question-placeholder",
    helper : "clone"
});
*/

$("#ProductOptionEdit #optioneditok").click(function() {

    var PostData = { action : "calculateoptionrow"};
    
    PostData.optionid = <?php if(isset($_GET["optionid"])) { echo $_GET["optionid"]; } else { echo "NewOptionID"; } ?>;
    PostData.name = $("#ProductOptionEdit #OptionName").val();
    PostData.sequence = Sequence;
    PostData.type = Type;
  
    switch(Type)
    {
    case "text":
    
        PostData["default"] = $("#ProductOptionEdit #OptionDefaultText").val();
        PostData.validation = $("#ProductOptionEdit #OptionValidationText").val();
        PostData.validationmessage = $("#ProductOptionEdit #OptionValidationMessageText").val();
        PostData.pricemodifier = $("#ProductOptionEdit #OptionPriceText").val();    
        break;

    case "textarea":
    
        PostData["default"] = $("#ProductOptionEdit #OptionDefaultTextArea").val();
        PostData.validation = $("#ProductOptionEdit #OptionValidationTextArea").val();
        PostData.validationmessage = $("#ProductOptionEdit #OptionValidationMessageTextArea").val();
        PostData.pricemodifier = $("#ProductOptionEdit #OptionPriceTextArea").val();
        break;
        
    case "select":
    
        PostData["default"] = "";
        PostData.validation = "";
        PostData.validationmessage = $("#ProductOptionEdit #OptionValidationMessageSelect").val();
        PostData.pricemodifier = "";
        
        var Values = "";
        
        $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR").each(function() {
        
            var This = $(this);
        
            Values += "value[]=" + encodeURIComponent(This.find("INPUT[name=value]").val()) + "&";            
            Values += "default[]=" + (This.find("INPUT[name=defaultvalue]:checked").length > 0 ? "true" : "false") + "&";
            Values += "valid[]=" + (This.find("INPUT[name=validvalue]:checked").length > 0 ? "true" : "false") + "&";
            Values += "valueprice[]=" + encodeURIComponent(This.find("INPUT[name=pricemodifiervalue]").val());
            Values += "&&";
        
        });
        
        Values = Values.replace(/&&$/,"");
     
        PostData.values = Values;
        
        break;
        
    case "checkbox":
    
        PostData["default"] = $("#ProductOptionEdit #OptionDefaultCheckbox:checked").length > 0;
        PostData.validation = $("#ProductOptionEdit #OptionValidationCheckbox").val();
        PostData.validationmessage = $("#ProductOptionEdit #OptionValidationMessageCheckbox").val();
        PostData.pricemodifier = $("#ProductOptionEdit #OptionPriceCheckbox").val();        
        break;    
        
    case "file":
    
        PostData["default"] = "";
        PostData.validation = $("#ProductOptionEdit #OptionValidationFile").val();
        PostData.validationmessage = $("#ProductOptionEdit #OptionValidationMessageFile").val();
        PostData.pricemodifier = $("#ProductOptionEdit #OptionPriceFile").val();        
        break;        
    }

    $.ajax({
        type : "POST",
        url : "../wp-content/plugins/wp-ezimerchant/wp-ezimerchant-ajax.php",
        data : PostData,
        cache : false,
        dataType : "html",
        success : function(html)
        {	
            if($("#ezi-productoption-table > TBODY > TR:has(TD:first-child > INPUT[name='optionid[]'][value=" + PostData.optionid + "])").length != 0)
            {
                $("#ezi-productoption-table > TBODY > TR:has(TD:first-child > INPUT[name='optionid[]'][value=" + PostData.optionid + "])").replaceWith(html);
				
                eziProductOptionBind($("#ezi-productoption-table > TBODY > TR:has(TD:first-child > INPUT[name='optionid[]'][value=" + PostData.optionid + "])"));		
            }
            else
            {
                $("#ezi-productoption-table > TBODY > TR:last-child").after(html);
                
                eziProductOptionBind($("#ezi-productoption-table > TBODY > TR:last-child"));        
                
                $("#ezi-productoption-table > TBODY > TR:has(TD.EmptyTable)").remove();            
            }
            $("#ezi-productoption-table > TBODY > TR:last-child > TD:first-child + TD > INPUT,#ezi-productoption-table > TBODY > TR > TD:first-child + TD > TEXTAREA,#ezi-productoption-table > TBODY > TR > TD:first-child + TD > SELECT").click(function(evt) {
            
                evt.preventDefault();
                evt.stopPropagation();
            
            });
			
			NewOptionID--;
            $.fn.fancybox.close();
        }
    });
});

$("#ProductOptionEdit #optioneditcancel").click(function() {


    $.fn.fancybox.close();
});

function ProductOptionDefaultChange()
{
    $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR > TD > INPUT[name=validvalue]").appendTo("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR:has(TD > INPUT[name=defaultvalue]:checked) > TD:first-child + TD + TD");
}

$("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR > TD > INPUT[name=defaultvalue]").click(ProductOptionDefaultChange);

$("#ProductOptionEdit #ProductOptionSelect #ProductOptionSelectNewValue").click(function() {

    var Default = $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR > TD > INPUT[name=defaultvalue]:checked").length == 0;

    $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY").append("<tr><td><input type=\"text\" name=\"value\"/></td><td><input type=\"radio\" name=\"defaultvalue\" " + (Default ? "checked=\"checked\"" : "") + "/></td><td>" + (Default ? "<input type=\"checkbox\" name=\"validvalue\" checked=\"checked\"/>" : "") + "</td><td><input type=\"text\" name=\"pricemodifiervalue\"/></td><td><input type=\"checkbox\"/></td></tr>");
    
    $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR > TD > INPUT[name=defaultvalue]").click(ProductOptionDefaultChange);
    
    $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR:last-child > TD:first-child > INPUT").focus();
});

$("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > THEAD > TR > TH:last-child > INPUT[type=checkbox]").click(function() {

    $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR > TD:last-child > INPUT[type=checkbox]").attr("checked", this.checked ? "checked" : "");

});

$("#ProductOptionEdit #ProductOptionSelect #ProductOptionSelectRemove").click(function() {

    $("#ProductOptionEdit #ProductOptionSelect > DIV > TABLE > TBODY > TR:has(TD:last-child > INPUT[type=checkbox]:checked)").remove();

});
</script>

<h1 style="font-size: 110%; margin: 0; padding: 1px;"><? if($_GET["view"] == "option-edit") { echo "Edit Question"; } else { echo "New Question"; } ?></h1>    

<label for="OptionName">Question Label</label> <input id="OptionName" type="text" value="<?=$OptionName?>"/>

<div class="Tabs">
    <ul>
		<li>Line of text</li>
		<li>Multiple Lines of text</li>
		<li>Option list</li>
		<li>Check box</li>
		<li>File upload</li>
	</ul>
	<div class="verttabs-div-container">
	    <div>
	        <table>
	        <tbody>
	        <tr>
	            <td><label for="OptionDefaultText">Default Value</label></td>
	            <td><input type="text" id="OptionDefaultText" value="<?=$OptionDefault?>"/></td>
	        </tr>
	        <!-- tr>
	            <td><label for="OptionValidationText">Validation</label></td>
	            <td>
	                <select id="OptionValidationText">
	                    <option value="">No validation</option>
	                    <option value="required" <? if($OptionValidation == "required") {?> selected="selected"<? } ?>>Value required</option>
	                    <option value="numeric" <? if($OptionValidation == "numeric") {?> selected="selected" <? } ?>>Value must be numeric</option>
	                </select>
	            </td>
	        </tr>
	        <tr>
	            <td><input type="text" id="OptionValidationMessageText" value="<?=$OptionValidationMessage?>"/></td>
	        </tr -->
	        <tr>
	            <td><label for="OptionPriceText">Price Change</label></td>
	            <td><input type="text" id="OptionPriceText" value="<? if($OptionPrice) { ?><?= $OptionPrice ?><? } ?>"/></td>
	        </tr>
	        </tbody>
	        </table>
	    </div>
	    <div>
	        <table>
	        <tbody>
	        <tr>
	            <td><label for="OptionDefaultTextArea">Default Value</label></td>
	            <td><textarea id="OptionDefaultTextArea"><?=$OptionDefault?></textarea></td>
	        </tr>
	        <!-- tr>
	            <td><label for="OptionValidationTextArea">Validation</label></td>
	            <td>
	                <select id="OptionValidationTextArea">
	                    <option>No validation</option>
	                    <option value="required" {if OptionValidation = "required"}selected="selected"{/if}>Value required</option>
	                </select>
	            </td>
	        </tr>
	        <tr>
	            <td><label for="OptionValidationMessageTextArea">Validation Message</label></td>
	            <td><input type="text" id="OptionValidationMessageTextArea" value="{OptionValidationMessage}"/></td>
	        </tr -->
	        <tr>
	            <td><label for="OptionPriceTextArea">Price Change</label></td>
	            <td><input type="text" id="OptionPriceTextArea" value="<? if($OptionPrice) { ?><?= $OptionPrice ?><? } ?>"/></td>
	        </tr>	        
	        </tbody>
	        </table>
	    </div>
	    <div id="ProductOptionSelect">
	        <input style="width: 80px;" id="ProductOptionSelectNewValue" type="button" class="Std Action" value="New" />
	        	 
            <div>
	        <table class="DataTable" style="width: 100%;">
	        <thead>
	        <tr>
	            <th style="background-color: #DFDFDF; padding: 6px; text-align: left;">Value</th>
	            <th style="background-color: #DFDFDF; padding: 6px; text-align: left;">Default</th>
	            <th style="background-color: #DFDFDF; padding: 6px; text-align: left;">Valid</th>
	            <th style="background-color: #DFDFDF; padding: 6px; text-align: left;">Price</th>
	            <th style="background-color: #DFDFDF; padding: 6px; text-align: center;"><input type="checkbox" /></th>
	        </tr>
	        </thead>
	        <tbody>
	        <? $valueidx = 0; foreach($OptionValues["value"] as $optionValue) { ?>
	        <tr>
	            <td><input type="text" name="value" value="<?=htmlspecialchars($optionValue)?>" /></td>
	            <td><input type="radio" name="defaultvalue" <? if($OptionValues["default"][$valueidx]) { ?>checked="checked"<? } ?>/></td>
	            <td><? if($OptionValues["default"][$valueidx]) { ?><input type="checkbox" name="validvalue" <? if($OptionValues["valid"][$valueidx]) { ?>checked="checked"<? } ?> /><? } ?></td>
	            <td><input type="text" name="pricemodifiervalue" value="<? if($OptionValues["valueprice"][$valueidx]) { ?><?= $OptionValues["valueprice"][$valueidx] ?><? } ?>"/></td>
	            <td><input type="checkbox" /></td>
	        </tr>
	        <? $valueidx++; } ?>             
	        </tbody>
	        <tfoot>
	        <tr>
	            <td colspan="5">
	            <div style="float: right;"><input style="width: 80px;" id="ProductOptionSelectRemove" type="button" class="Std Action" value="Remove" /></div>
	            </td>
	        </tr>
	        </tfoot>
	        </table>
	        </div>	        
	        
	        <!-- label for="OptionValidationMessageSelect">Validation Message</label>
            <input type="text" id="OptionValidationMessageSelect" value="{OptionValidationMessage}"/ -->
	    </div>
	    <div>
	        <table>
	        <tbody>
	        <tr>
	            <td><label for="OptionDefaultCheckbox">Default Value</label></td>
	            <td><input type="checkbox" id="OptionDefaultCheckbox" <? if($OptionDefault) {?> checked="checked" <? } ?>/></td>
	        </tr>
	        <!-- tr>
	            <td><label for="OptionValidationCheckbox">Validation</label></td>
	            <td>
	                <select id="OptionValidationCheckbox">
	                    <option value="">No validation</option>
	                    <option value="required" {if OptionValidation="required"}selected="selected"{/if}>Must be checked</option>
	                    <option value="notrequired" {if OptionValidation="notrequired"}selected="selected"{/if}>Must be unchecked</option>
	                </select>
	            </td>
	        </tr>
	        <tr>
	            <td><label for="OptionValidationMessageCheckbox">Validation Message</label></td>
	            <td><input type="text" id="OptionValidationMessageCheckbox" value="{OptionValidationMessage}"/></td>
	        </tr -->
	        <tr>
	            <td><label for="OptionPriceCheckbox">Price Change</label></td>
	            <td><input type="text" id="OptionPriceCheckbox" value="<? if($OptionPrice) { ?><?= $OptionPrice ?><? } ?>"/></td>
	        </tr>
	        </tbody>
	        </table>
	    </div>		
	    <div>
	        <table>
	        <tbody>
	        <!-- tr>
	            <td><label for="OptionValidationFile">Validation</label></td>
	            <td>
	                <select id="OptionValidationFile">
	                    <option value="">No validation</option>
	                    <option value="required" {if OptionValidation="required"}selected="selected"{/if}>Must submit file</option>
	                </select>
	            </td>
	        </tr>
	        <tr>
	            <td><label for="OptionValidationMessageFile">Validation Message</label></td>
	            <td><input type="text" id="OptionValidationMessageFile" value="{OptionValidationMessage}"/></td>
	        </tr -->
	        <tr>
	            <td><label for="OptionPriceFile">Price Change</label></td>
	            <td><input type="text" id="OptionPriceFile" value="<? if($OptionPrice) { ?><?= $OptionPrice ?><? } ?>"/></td>
	        </tr>
	        </tbody>
	        </table>
	    </div>	
	</div>     
</div>        

<div style="text-align: right; padding: 6px;">
    <input id="optioneditok" style="min-width: 80px;" type="button" class="Std Action" value="OK" />
    <input id="optioneditcancel" style="min-width: 80px;" type="button" class="Std Action" value="Cancel" />
</div>

</div>
