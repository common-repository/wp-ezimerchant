<?php
/*
Plugin Name: wp-ezimerchant
Plugin URI: https://www.ezimerchant.com/wordpress/
Description: Sell products online immediately using Ezimerchants globally recognised ecommerce solution. Easily process payments using PayPal, credit card or other supported options, and as a PayPal preferred ecommerce partner you can count on Ezimerchants experience and easy friendly support. Maintain your site theme throughout your checkout and comply with the Payment Card Industry Data Security Standard (PCI DSS - https://www.pcisecuritystandards.org/), a must to ensure customer confidence when purchasing on your site!
Version: 1.5.2
Author: ezimerchant
Author URI: https://www.ezimerchant.com/
License: GPL2
*/
/*  Copyright 2010  OnTechnology Pty Ltd  (info@ontech.com.au)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*	define global vars - these vars need 'global' keyword otherwise they're only kept in 'activate_plugin' function scope - PS
	see: http://wordpress.org/support/topic/register_activation_hook-and-global-variables */
global $ezi_m_host;
global $wpezimerchantversion;
$wpezimerchantversion = "1.5.2";
$ezi_m_host = "secure.ezimerchant.com";

define('WP_EZI_M_DEV', false /* Remember to set to false before releasing! Will commit to SVN as true however */);
if(WP_EZI_M_DEV)
{
	// dev mode overrides
	$ezi_m_host = "beta.ezimerchant.com";
}

function wp_ezi_m_product_form($post)
{
  global $wpdb;

  $wpdb->show_errors();
  
  $table = $wpdb->prefix . "ezi_m_merchant";	
  $token = $wpdb->get_var("SELECT Token FROM $table");

  if(!$token)
  {
?>
	<a href="admin.php?page=ezi-m-menu">Link your ezimerchant account to WordPress - ecommerce enable your blog</a>
<?  
  	return;
  }
  $table = $wpdb->prefix . "ezi_m_product";
  $prod = $wpdb->get_row( $wpdb->prepare("SELECT ID, Code, Name, Price, TaxID, Enabled FROM $table WHERE ID = %d", $post->ID) );
?>

<input type="checkbox" id="ezi-product-is" name="ezi-product-is" onclick="if(this.checked) jQuery('#ezi-product-info').slideDown('fast'); else jQuery('#ezi-product-info').slideUp('fast');" <?if($prod && $prod->Enabled ==1) {?>checked="checked"<?}?>/>
<label for="ezi-product-is">Is this post a product?</label>

<div id="ezi-product-info" <?if(!$prod || $prod->Enabled == 0) {?>style="display: none; overflow: auto;"<?} else {?>style="overflow:auto;"<?}?>>
	<table style="width: 100%;">
	<tbody>
	<tr>
		<td style="width: 40%; vertical-align: top; padding: 5px;">
	<table id="ezi-product-table">
	  <tr>
	    <td>
	      <label for="ezi-product-code">Code</label>
	    </td>
	    <td>
	      <input type="text" id="ezi-product-code" name="ezi-product-code" value="<?=htmlspecialchars($prod->Code)?>" size="50"/>
	    </td>
	  </tr>
	  <tr>
	    <td>
	      <label for="ezi-product-name">Name</label>
	    </td>
	    <td>
	      <input type="text" id="ezi-product-name" name="ezi-product-name" value="<?print(htmlspecialchars($prod->Name))?>" size="50"/>
	    </td>
	  </tr>
	  <tr>
	    <td>
	      <label for="ezi-product-price" name="ezi-product-price">Price</label>
	    </td>
	    <td>
	      <input type="text" id="ezi-product-price" name="ezi-product-price" value="<?print(htmlspecialchars(number_format($prod->Price * 1.1, 2)))?>"/>
	    </td>
	  </tr>
	  <tr>
	    <td>
	      <label for="ezi-product-tax">Tax</label>
	    </td>
	    <td>
	      <select id="ezi-product-tax" name="ezi-product-tax">
	        <option value="1" <?if($prod->TaxID == 1) { print "selected=\"selected\""; }?>>GST</option>
	        <option value="2" <?if($prod->TaxID == 2) { print "selected=\"selected\""; }?>>GST Free</option>
	      </select>
	    </td>
	  </tr>
	</table>		
		</td>
		<td style="width: 60%; vertical-align: top; padding: 5px;">
	<h4 style="margin: 0; padding: 0; margin-bottom: 3px;">Product Options</h4>
	
	<?
	$optiontable = $wpdb->prefix . "ezi_m_productoption";	
	$valuetable = $wpdb->prefix . "ezi_m_productoptionvalue";
	if($prod)
	{
		$options = $wpdb->get_results($wpdb->prepare("SELECT ID, Name, Type, DefaultVal, AffectsPrice, PriceModifier FROM $optiontable WHERE ProductID = %d ORDER BY Sequence", $post->ID));		
	}
	else
	{
		$options = array();
	}	
	?>
	
	<table id="ezi-productoption-table" style="border-collapse: collapse; max-width: 500px; width: 100%; border: 1px solid #DFDFDF;">
	<thead>
		<tr>
			<th colspan="3" style="text-align: left; font-weight: normal; padding: 6px;"><input id="ezi-productoption-new" style="min-width: 80px;"  type="button" value="New"/></th>
		</tr>
		<tr>
			<th style="background-color: #DFDFDF; padding: 6px; text-align: left; background-image: url(wp-admin/images/gray-grad.png);">Question</th>
			<th style="background-color: #DFDFDF; padding: 6px; text-align: left;">Example</th>
			<th style="text-align: center; background-color: #DFDFDF; padding: 4px;"><input type="checkbox"/></th>
		</tr>
	</thead>
	<tbody style="border-bottom: 1px solid #DFDFDF;">	
	<? if (count($options) == 0) { ?>
		<tr>
			<td class="EmptyTable" style="padding: 7px; text-align: center;" colspan="3">&lt;&lt; No Options &gt;&gt;</td>
		</tr>
	<? } else { ?>
	<? $optidx = 0; foreach ($options as $opt) { 
	
		$values = $wpdb->get_results($wpdb->prepare("SELECT Value, IsDefault, PriceModifier  FROM $valuetable WHERE OptionID = %d ORDER BY Sequence", $opt->ID));
		
		$valobj = array("value" => array(), "default" => array(), "valueprice" => array(), "valid" => array());		
		$validx = 0;
		foreach($values as $value)
		{
			$valobj["value"][$validx] = $value->Value;
			$valobj["default"][$validx] = $value->IsDefault;
			$valobj["valueprice"][$validx] = $value->PriceModifier;
			$valobj["valid"][$validx] = true;
			
			$validx++;
		}
	
	?>
	<tr>
		<td style="vertical-align: middle; padding: 6px; cursor: pointer;"><a href="#"><?=htmlspecialchars($opt->Name)?></a>
			<input type="hidden" name="optionid[]" value="<?=$opt->ID?>"/>
			<input type="hidden" name="optionname[]" value="<?=htmlspecialchars($opt->Name)?>"/>
			<input type="hidden" name="optiontype[]" value="<?=htmlspecialchars($opt->Type)?>"/>
			<input type="hidden" name="optiondefault[]" value="<?=htmlspecialchars($opt->DefaultVal)?>"/>
			<input type="hidden" name="optionprice[]" value="<?=htmlspecialchars($opt->PriceModifier)?>"/>
			<input type="hidden" name="optionvalues[]" value="<?=htmlspecialchars(http_build_query($valobj))?>"/>
		</td>
		<td style="vertical-align: top; padding: 6px; cursor: pointer;">
		<? 	if($opt->Type == "text") 
						{
					?>
						<input type="text" value="<?=htmlspecialchars($opt->DefaultVal)?>"/>
					<? 
						}
						elseif($opt->Type == "textarea") 
						{
					?>
						<textarea><?=htmlspecialchars($opt->DefaultVal)?></textarea>
					<? 
						}
						elseif($opt->Type == "select") 
						{
							$values = $wpdb->get_results($wpdb->prepare("SELECT Value, IsDefault, PriceModifier  FROM $valuetable WHERE OptionID = %d ORDER BY Sequence", $opt->ID));		
					?>
					
						<select readonly="readonly" onclick="return false;">
							<? foreach($values as $value) { ?>
							<option <? if($value->IsDefault) { ?>selected="selected"<? } ?>><?=htmlspecialchars($value->Value)?></option>
							<? } ?>
						</select>
					<? 
						}
						elseif($opt->Type == "checkbox") 
						{
					?>
						<input type="checkbox" <? if($_POST["default"]) { ?>checked="checked"<? } ?>/>
					<? 
						}
						elseif($opt->Type == "file") 
						{
					?>
						<input type="file" />
					<?
						}
					?>		
		</td>
		<td style="text-align: center;"><input type="checkbox"/></td>
	</tr>	
	<? $optidx++; }
	} ?>
	</tbody>
	<tfoot>
		<tr>
			<td style="text-align: right; padding: 6px;" colspan="3"><input id="ezi-productoption-remove" style="min-width: 80px;" type="button" value="Remove"/></td>
		</tr>
	</tfoot>
	</table>
	
		</td>
	</tr>
	</table>

</div>
  <?
}

function wp_ezi_m_post_product_form($post)
{
	wp_ezi_m_product_form($post);
}

function wp_ezi_m_page_product_form($post)
{
	wp_ezi_m_product_form($post);
}

function wp_ezi_m_init()
{
	global $ezi_m_host;
	
	add_meta_box('ezi-product', 'Product Information', 'wp_ezi_m_post_product_form', 'post', 'normal', 'high');
	add_meta_box('ezi-product', 'Product Information', 'wp_ezi_m_page_product_form', 'page', 'normal', 'high');	

	wp_register_script("ezimerchant", "https://" . $ezi_m_host . "/wordpress/ezi.js", array("jquery"), "1.04");	
	wp_register_script("verttabs", "https://" . $ezi_m_host . "/wordpress/jquery.verttabs.js", array("jquery"), "1.00");
	wp_register_script("fancybox", "https://" . $ezi_m_host . "/wordpress/fancybox/jquery.fancybox.js", array("jquery"), "1.00");
}

function wp_ezi_m_admin_menu()
{
  $page = add_menu_page('Store', 'Store', 'manage_options', 'ezi-m-menu', 'wp_ezi_m_menu', '', 27);
  
  add_action("admin_print_scripts-" .$page, "wp_ezi_m_admin_script");  
  
  $page = add_submenu_page('ezi-m-menu', 'Sales', 'Sales', 'manage_options', 'ezi-m-menu', 'wp_ezi_m_menu');
  
  add_action("admin_print_scripts-" .$page, "wp_ezi_m_admin_script");
  
  add_action("admin_print_scripts", "wp_ezi_m_admin_script");
  
//  $page = add_submenu_page('ezi-m-menu', 'Products', 'Products', 'manage_options', 'ezi-m-menu-products', 'wp_ezi_m_menu_products');
  
//  add_action("admin_print_scripts-" .$page, "wp_ezi_m_admin_script");  
  
//  $page = add_submenu_page('ezi-m-menu', 'Store Settings', 'Store Settings', 'manage_options', 'ezi-m-menu-settings', 'wp_ezi_m_menu_settings');
  
//  add_action("admin_print_scripts-" .$page, "wp_ezi_m_admin_script");  
}

function wp_ezi_m_admin_script()
{
	wp_enqueue_script("ezimerchant");
	wp_enqueue_script("verttabs");
	wp_enqueue_script("fancybox");
}

function wp_ezi_m_admin_style()
{	
	global $ezi_m_host;
	
	wp_enqueue_style("ezimerchant", "https://" . $ezi_m_host . "/wordpress/ezi.css", false, "1.0", "screen");
	wp_enqueue_style("verttabs", "https://" . $ezi_m_host . "/wordpress/verttabs.css", false, "1.0", "screen");
	wp_enqueue_style("fancybox", "https://" . $ezi_m_host . "/wordpress/fancybox/jquery.fancybox.css", false, "1.0", "screen");
}

function wp_ezi_m_menu()
{
	global $ezi_m_host;
	global $wpdb;
	global $wpezimerchantversion;

	$merchanttable = $wpdb->prefix . "ezi_m_merchant";	
	$token = $wpdb->get_var("SELECT Token FROM $merchanttable");
	$signkey = $wpdb->get_var("SELECT SigningKey FROM $merchanttable"); 
	
	$email = $wpdb->get_var("SELECT user_email FROM $wpdb->users ORDER BY ID LIMIT 1");

	$requestData = array("adminhref"=>$_GET["adminhref"]);	 	
	
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$requestData["method"] = "POST";
		$requestData["body"] = file_get_contents("php://input");
	}
	else
	{
		$requestData["method"] = "GET";
	}	


	if(!$token)
	{
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(isset($_GET["associate"]))
			{
				$associate = split("\\|", $_POST["merchant"]);
				
				$token = $associate[1];
				$signkey = $associate[2];
				
				$wpdb->show_errors();
			
				$wpdb->query("DELETE FROM $merchanttable");
				$wpdb->query($wpdb->prepare("INSERT INTO $merchanttable (MerchantID, Token, SigningKey) VALUES(%d, %s, %s)", $associate[0], $associate[1], $associate[2]));
				
				$requestData = array();
				$requestData["headers"] = $headers;
				$requestData["method"] = "GET";
				$requestData["body"] = null;
			}
			else
			{
				$requestData = array();
				$requestData["method"] = "POST";
				$requestData["body"] = file_get_contents("php://input");
	
				$requestData["sslverify"] = false;
				$result = wp_remote_request("https://" . $ezi_m_host . "/wordpress/", $requestData);
				if(is_wp_error($result))
				{
					// TODO: We need to be handling failed remote requests! - PS
				}
				echo wp_remote_retrieve_body($result);
				return;
			}	
		}
		else
		{
		?>
			<div class="wrap"><h2>Log in to ezimerchant</h2></div>
			<form method="post">
				<input type="hidden" name="action" value="login">
				<table>
					<tr>
						<td>Email:</td>
						<td><input type="text" name="email" value="<?= $email ?>" size="40"></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input type="password" name="password" size="40"></td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right">
							<input type="submit" value="Login">
						</td>
					</tr>
				</table>
			</form>
		<?
			return;
		}
	}
		
	$headers = array("X-ezimerchant-wp-key" => $signkey, "X-ezimerchant-wp-content-url" => WP_CONTENT_URL."/plugins/".dirname(plugin_basename(__FILE__)), "X-ezimerchant-wp-version" => $wpezimerchantversion);
	$requestData["headers"] = $headers;	
	
	$uri = $_GET['adminhref'];
	if(!$uri)
		$uri = "/orders/";	
	$uri = preg_replace('/!/', '?', $uri);
	$requestData["sslverify"] = false;
	$result = wp_remote_request("https://" . $ezi_m_host . "/wordpress/$token$uri", $requestData);
	if(is_wp_error($result))
	{
		// TODO: We need to be handling failed remote requests! - PS
	}	
	echo wp_remote_retrieve_body($result);

	if(wp_remote_retrieve_response_code($result) == 403)
	{
		$table = $wpdb->prefix . "ezi_m_merchant";
		$wpdb->query("UPDATE $table SET Token = ''");
	}
	
	if(isset($result["headers"]["x-ezimerchant-wp-upgrade"]))
	{
		wp_ezi_m_upgrade();	
	}
}

function wp_ezi_m_menu_products()
{
  global $wpdb;
  
  $wpdb->show_errors();

  $table = $wpdb->prefix . "ezi_m_product";
  $posttable = $wpdb->posts;

  $products = $wpdb->get_results("SELECT P.ID, P.Code, P.Name, P.Price, P.TaxID, P.SKUID, P.Enabled FROM $table AS P JOIN $posttable AS A ON A.ID = P.ID AND A.post_type = 'post' AND A.post_status IN ('publish', 'private', 'draft')");

  echo "<table>";

  foreach($products as $product) {
  echo "<tr>
    <td>".$product->ID."</td>
    <td>".$product->Code."</td>
    <td>".$product->Name."</td>
    <td>".$product->Price."</td>
	<td>".$product->TaxID."</td>
	<td>".$product->SKUID."</td>
	<td>".$product->Enabled."</td>
  </tr>";
  }

  echo "</table>";
}

function wp_ezi_m_menu_settings()
{
	global $wpdb;
	
	$wpdb->show_errors();

	echo "settings";	
}

function wp_ezi_m_save_post($postid, $post)
{
	if($_POST['action'] != 'editpost')
		return;

	global $wpdb;

    $wpdb->show_errors();
	
    $code = $_POST['ezi-product-code'];
    $name = $_POST['ezi-product-name'];
    $price = floatval($_POST['ezi-product-price']);
    $taxid = $_POST['ezi-product-tax'];

	$price = $price / 1.1;
	

	if(isset($_POST['ezi-product-is']))
	{
	    $table = $wpdb->prefix . "ezi_m_product";

	    if($wpdb->get_var($wpdb->prepare("SELECT ID FROM $table WHERE ID = %d", $postid)) == $postid)
		{
		    $wpdb->query( $wpdb->prepare("UPDATE $table SET Code = %s, Name = %s, Price = %f, TaxID = %d, Enabled = 1 WHERE ID = %d", $code, $name, $price, $taxid, $postid) );
		}
		else
		{
		    $wpdb->query( $wpdb->prepare("INSERT INTO $table (ID, Code, Name, Price, TaxID) VALUES (%d, %s, %s, %f, %d)", $postid, $code, $name, $price, $taxid) );
		}
		
	    $optiontable = $wpdb->prefix . "ezi_m_productoption";
	    $optionvaluetable = $wpdb->prefix . "ezi_m_productoptionvalue";
	    $wpdb->query( $wpdb->prepare("DELETE FROM $optionvaluetable WHERE OptionID IN (SELECT ID FROM $optiontable WHERE ProductID = %d)", $postid) );
	    $wpdb->query( $wpdb->prepare("DELETE FROM $optiontable WHERE ProductID = %d", $postid) );

		if(isset($_POST["optionid"]))
		{		
			$optionidx = 0;
			foreach($_POST["optionid"] as $optionid)
			{
				$wpdb->query( $wpdb->prepare("INSERT INTO $optiontable (ProductID, Name, Type, DefaultVal, AffectsPrice, PriceModifier, Sequence) VALUES (%d, %s, %s, %s, %d, %s, %d)", 
					$postid, $_POST["optionname"][$optionidx], $_POST["optiontype"][$optionidx], $_POST["optiondefault"][$optionidx], 1, $_POST["optionprice"][$optionidx], $optionidx));
					
				$optiondbid = $wpdb->insert_id;
			
				if(isset($_POST["optionvalues"]) && isset($_POST["optionvalues"][$optionidx]))
				{
					parse_str($_POST["optionvalues"][$optionidx], $values);
					
					$valueidx = 0;
					
					if(isset($values["value"]))
					{
						foreach($values["value"] as $value)
						{
							$wpdb->query( $wpdb->prepare("INSERT INTO $optionvaluetable (OptionID, Value, IsDefault, PriceModifier, Sequence) VALUES (%d, %s, %d, %s, %d)",
								$optiondbid, $value, $values["default"][$valueidx], $values["valueprice"][$valueidx], $valueidx));
								
							$valueidx++;
						}				
					}
				}
			

				$optionidx++;
			}
		}
		
	    if($post->post_type != 'revision')
	    {
	      wp_ezi_m_post_product($postid, time() );
	    }
		
		
	}
	else
	{
	    $table = $wpdb->prefix . "ezi_m_product";

	    if($wpdb->get_var($wpdb->prepare("SELECT ID FROM $table WHERE ID = %d", $postid)) == $postid)
		{
		    $wpdb->query( $wpdb->prepare("UPDATE $table SET Code = %s, Name = %s, Price = %f, TaxID = %d, Enabled = 0 WHERE ID = %d", $code, $name, $price, $taxid, $postid) );
		}

	    if($post->post_type != 'revision')
	    {
	      wp_ezi_m_post_product($postid, time() );
	    }
	}
}

function wp_ezi_m_post_product($productid, $when)
{
	global $ezi_m_host;
	global $wpdb;
	global $wpezimerchantversion;
	
	$merchanttable = $wpdb->prefix . "ezi_m_merchant";	
	$token = $wpdb->get_var("SELECT Token FROM $merchanttable");
	$signkey = $wpdb->get_var("SELECT SigningKey FROM $merchanttable"); 

	$producttable = $wpdb->prefix . "ezi_m_product";	
	$product = $wpdb->get_row($wpdb->prepare("SELECT ID, Code, Name, Price, TaxID, SKUID, Enabled, 'productupdate' AS action FROM $producttable WHERE ID = %d", $productid));
	$optiontable = $wpdb->prefix . "ezi_m_productoption";	
	$product->options = $wpdb->get_results($wpdb->prepare("SELECT ID, Name, Type, DefaultVal, PriceModifier FROM $optiontable WHERE ProductID = %d ORDER BY Sequence", $productid));

	$valuetable = $wpdb->prefix . "ezi_m_productoptionvalue";	
	foreach ($product->options as $opt) 
	{
		$opt->values = $wpdb->get_results($wpdb->prepare("SELECT Value FROM $valuetable WHERE OptionID = %d ORDER BY Sequence", $opt->ID));
	}		 

	$headers = array("Content-Type" => "application/json", "X-ezimerchant-wp-key" => $signkey, "X-ezimerchant-wp-version" => $wpezimerchantversion);
	
	$result = wp_remote_request("https://" . $ezi_m_host . "/wordpress/$token/", array("method" => "POST", "body" => json_encode($product), "headers" => $headers, "sslverify" => false));
	if(is_wp_error($result))
	{
		// TODO: We need to be handling failed remote requests! - PS
	}
	
	if(wp_remote_retrieve_response_code($result) == 403)
	{
		$table = $wpdb->prefix . "ezi_m_merchant";
		$wpdb->query("UPDATE $table SET Token = ''");
	}
	
	$upgrade = false;
	if(isset($result["headers"]["x-ezimerchant-wp-upgrade"]))
		$upgrade = true;

	$result = json_decode(wp_remote_retrieve_body($result));

	$wpdb->query($wpdb->prepare("UPDATE $producttable SET SKUID = %d WHERE ID = %d", $result->skuid, $productid));
	
	if($upgrade)
	{
		wp_ezi_m_upgrade();	
	}	
}


function wp_ezi_m_the_content($content)
{
	global $wpdb;
	global $id;
	global $wp_current_filter;

	if(in_array('get_the_excerpt', $wp_current_filter))
		return $content;
	
	$wpdb->show_errors();

	$merchanttable = $wpdb->prefix . "ezi_m_merchant";
	$table = $wpdb->prefix . "ezi_m_product";
	
	$prod = $wpdb->get_row( $wpdb->prepare("SELECT ID, Code, Name, Price, TaxID, SKUID, Enabled FROM $table WHERE ID = %d AND Enabled != 0", $id) );
	if(!$prod)
		return $content;
	
	if(preg_match("/ezibuy|ezicart|ezicheckout/", $content))
		return $content;
	
	$token = $wpdb->get_var("SELECT Token FROM $merchanttable");
	
	if(file_exists(WP_PLUGIN_DIR."/wp-ezimerchant/wp-template/product.template"))
		$template = file_get_contents(WP_PLUGIN_DIR."/wp-ezimerchant/wp-template/product.template");
	else
		$template = "[eziproduct]";

	ob_start();
	eval("?>$template<?php ");
	$productcontent = ob_get_contents();
	ob_end_clean();	
	
	return "<div class=\"hproduct\">$content $productcontent</div>";
}

function wp_ezi_m_the_excerpt($excerpt)
{
	global $wpdb;
	global $id;

	$merchanttable = $wpdb->prefix . "ezi_m_merchant";
	$table = $wpdb->prefix . "ezi_m_product";
	
	$prod = $wpdb->get_row( $wpdb->prepare("SELECT ID, Code, Name, Price, TaxID, SKUID, Enabled FROM $table WHERE ID = %d AND Enabled != 0", $id) );
	if(!$prod)
		return $excerpt;
	
	if(preg_match("/ezibuy|ezicart|ezicheckout/", $excerpt))
		return $excerpt;
	
	$token = $wpdb->get_var("SELECT Token FROM $merchanttable");
	
	if(file_exists(WP_PLUGIN_DIR."/wp-ezimerchant/wp-template/productexcerpt.template"))
		$template = file_get_contents(WP_PLUGIN_DIR."/wp-ezimerchant/wp-template/productexcerpt.template");
	else if(file_exists(WP_PLUGIN_DIR."/wp-ezimerchant/wp-template/product.template"))
		$template = file_get_contents(WP_PLUGIN_DIR."/wp-ezimerchant/wp-template/product.template");
	else
		$template = "[eziproduct]";

	ob_start();
	eval("?>$template<?php ");
	$productcontent = ob_get_contents();
	ob_end_clean();
	
	$productcontent = do_shortcode($productcontent);

	return "<div class=\"hproduct\">$excerpt $productcontent</div>";
}

function wp_ezi_m_standardshortcode($filename)
{
	global $wpdb;
	global $id;

	$merchanttable = $wpdb->prefix . "ezi_m_merchant";
	$table = $wpdb->prefix . "ezi_m_product";
	$optiontable = $wpdb->prefix . "ezi_m_productoption";
	$optionvaluetable = $wpdb->prefix . "ezi_m_productoptionvalue";
	
	$token = $wpdb->get_var("SELECT Token FROM $merchanttable");
	$securedomain = "$token.ezimerchant.com";

	$prod = $wpdb->get_row( $wpdb->prepare("SELECT ID, Code, Name, Price, TaxID, SKUID, Enabled FROM $table WHERE ID = %d", $id) );
	if(!$prod)
		return "";
		
	$options = $wpdb->get_results($wpdb->prepare("SELECT ID, Name, Type, DefaultVal FROM $optiontable WHERE ProductID = %d ORDER BY Sequence", $id));
	
	foreach($options as $option)
	{
		$option->Values = $wpdb->get_results($wpdb->prepare("SELECT Value FROM $optionvaluetable WHERE OptionID = %d", $option->ID));
	}

	$productcode = htmlspecialchars($prod->Code);
	$productname = htmlspecialchars($prod->Name);
	$productprice = htmlspecialchars("$".number_format($prod->Price * 1.1, 2));
	$productpriceextax = htmlspecialchars("$".number_format($prod->Price, 2));
	$buybuttonid = $prod->SKUID;

	$template = file_get_contents(WP_PLUGIN_DIR."/wp-ezimerchant/wp-template/$filename.template");

	ob_start();
	eval("?>$template<?php ");
	$content .= ob_get_contents();
	ob_end_clean();	

	return do_shortcode($content);
}

function wp_ezi_m_product()
{
	return wp_ezi_m_standardshortcode("eziproduct");
}

function wp_ezi_m_code()
{
	return wp_ezi_m_standardshortcode("ezicode");
}

function wp_ezi_m_name()
{
	return wp_ezi_m_standardshortcode("eziname");
}


function wp_ezi_m_price()
{
	return wp_ezi_m_standardshortcode("eziprice");
}

function wp_ezi_m_buybutton()
{
	return wp_ezi_m_standardshortcode("ezibuy");
}

function wp_ezi_m_cart()
{
	return wp_ezi_m_standardshortcode("ezicart");
}

function wp_ezi_m_checkout()
{
	return wp_ezi_m_standardshortcode("ezicheckout");
}

function wp_ezi_m_edit_user_profile()
{
  echo "";
}

function wp_ezi_m_upgrade()
{
	global $ezi_m_host;
	global $wpdb;

//	trigger_error("Entering wp_ezi_m_upgrade");
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$result = wp_remote_request("https://" . $ezi_m_host . "/wordpress/wp-ezimerchant.php", array("sslverify" => false));
	if(is_wp_error($result))
	{
		// TODO: We need to be handling failed remote requests! - PS
	}
//	trigger_error(print_r($result, true));
	file_put_contents(WP_PLUGIN_DIR."/wp-ezimerchant/wp-ezimerchant.php", wp_remote_retrieve_body($result));
	// TODO: Error handling in the event we can't write the file - PS
	
	$result = wp_remote_request("https://" . $ezi_m_host . "/wordpress/dbupgrade", array("sslverify" => false));
	if(is_wp_error($result))
	{
		// TODO: We need to be handling failed remote requests! - PS
	}
//	trigger_error(print_r($result, true));
	$result = json_decode(wp_remote_retrieve_body($result));

	foreach($result as $update)
	{
		dbDelta(str_replace('#PREFIX#', $wpdb->prefix, $update));
	}
//	trigger_error("Leaving wp_ezi_m_upgrade");
}

function wp_ezi_m_activate()
{
	global $ezi_m_host;
	global $wpdb;
	global $wpezimerchantversion;	
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$table = $wpdb->prefix . "ezi_m_merchant";
	$sql = "CREATE TABLE ".$table." (
	MerchantID mediumint(9) NOT NULL,
	Token tinytext NOT NULL,
	SigningKey tinytext NOT NULL,
	UNIQUE KEY id (MerchantID)	
	)";

	dbDelta($sql);
	
	if($wpdb->get_var("SELECT 1 FROM $table LIMIT 1") != 1)
	{
		$email = $wpdb->get_var("SELECT user_email FROM $wpdb->users ORDER BY ID LIMIT 1");
		$headers = array("Content-Type" => "application/json", "X-ezimerchant-wp-version" => $wpezimerchantversion);
		$regdata = (object)array("action" => "register", "email" => $email);
		$result = wp_remote_request("https://" . $ezi_m_host . "/wordpress/", array("method" => "POST", "body" => json_encode($regdata), "headers" => $headers, "sslverify" => false));
		if(is_wp_error($result))
		{
			// TODO: We need to be handling failed remote requests! - PS
		}
//		trigger_error(print_r($regdata, true));
//		trigger_error(print_r($result, true));
		$result = json_decode(wp_remote_retrieve_body($result));
		// TODO: Check this out as the server is returning nothing when this is called! I have an email registered! - PS
		if(property_exists($result, 'merchantid') && $result->merchantid != 0)
			$wpdb->query($wpdb->prepare("INSERT INTO $table VALUES (%d, %s, %s)", $result->merchantid, $result->merchanttoken, $result->signkey));
	}
	
	// TODO: Review how the automatic updating works for potential issues - PS
	wp_ezi_m_upgrade();
}

function wp_ezi_m_deactivate()
{
	// TODO: handle anything that needs changing upon deactivation, maybe nothing. - PS
}

function wp_ezi_m_uninstall()
{
	// TODO: clean up sql and leave the house in order as if wp-ezimerchant never existed. - PS
}

add_action('admin_init', 'wp_ezi_m_init');
add_action('admin_menu', 'wp_ezi_m_admin_menu');
add_action('admin_print_styles', 'wp_ezi_m_admin_style');
add_action('save_post', 'wp_ezi_m_save_post', 1, 2);
add_action('edit_user_profile', 'wp_ezi_m_edit_user_profile');
add_filter('the_content', 'wp_ezi_m_the_content');
add_filter('the_excerpt', 'wp_ezi_m_the_excerpt');
add_shortcode('ezicode', 'wp_ezi_m_code');
add_shortcode('eziname', 'wp_ezi_m_name');
add_shortcode('eziprice', 'wp_ezi_m_price');
add_shortcode('eziproduct', 'wp_ezi_m_product');
add_shortcode('ezibuy', 'wp_ezi_m_buybutton');
add_shortcode('ezicart', 'wp_ezi_m_cart');
add_shortcode('ezicheckout', 'wp_ezi_m_checkout');

register_activation_hook(__FILE__, 'wp_ezi_m_activate');
register_deactivation_hook(__FILE__, 'wp_ezi_m_deactivate');
register_uninstall_hook(__FILE__, 'wp_ezi_m_uninstall');
?>