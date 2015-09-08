<?php
defined('_JEXEC') or die();

/**
 * @author Valérie Isaksen
 * @version $Id: render_pluginname.php 7198 2013-09-13 13:09:01Z alatak $
 * @package VirtueMart
 * @subpackage vmpayment
 * @copyright Copyright (C) 2004-Copyright (C) 2004-2015 Virtuemart Team. All rights reserved.   - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

?>
<span class="veritrans vmpayment">
	<span class="vmpayment_name"><?php echo $viewData['payment_name'] ?> </span>
	<?php
	if (!empty($viewData['payment_description'])) {
		?>
		<span class="vmpayment_description"> Description </span>
	<?php
	}
	?>
</span>



