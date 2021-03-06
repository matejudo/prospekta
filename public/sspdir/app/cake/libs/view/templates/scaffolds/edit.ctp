<?php
/* SVN FILE: $Id: edit.ctp 5118 2007-05-18 17:19:53Z phpnut $ */
/**
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs.view.templates.scaffolds
 * @since			CakePHP(tm) v 0.10.0.1076
 * @version			$Revision: 5118 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-05-18 12:19:53 -0500 (Fri, 18 May 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
?>
<?php
echo $form->create($modelClass);
echo $form->inputs($fieldNames);
echo $form->end(__('Save', true)); ?>

<div class="nav">
	<ul>
		<?php
			if($formName == 'Edit') {
				echo "<li>".$html->link(__('Delete  ', true).$humanSingularName, array('action' => 'delete', $data[$modelClass][$primaryKey]), null, 'Are you sure you want to delete '.$data[$modelClass][$displayField])."</li>";

				foreach($fieldNames as $field => $value) {
					if(isset($value['foreignKey'])) {
						echo '<li>' . $html->link(__('View ', true) . Inflector::humanize($value['controller']), array('action' => 'index')) . '</li>';
						echo '<li>' . $html->link(__('Add ', true) . Inflector::humanize($value['modelKey']), array('action' => 'add')) . '</li>';
					}
				}
			}

			echo "<li>".$html->link($humanPluralName, array('action' => 'index'))."</li>";

			foreach($linked as $name => $controller) {
				echo '<li>'.$html->link(Inflector::humanize($controller), array('controller'=> $controller)).'</li>';
			}
		?>
	</ul>
</div>