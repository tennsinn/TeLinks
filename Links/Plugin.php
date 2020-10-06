<?php
/**
 * 链接管理插件
 *
 * @package Links
 * @version 0.1.1
 * @author 息E-敛
 * @link http://tennsinn.com
 **/

class Links_Plugin implements Typecho_Plugin_Interface
{
	/** 激活插件方法 */
	public static function activate()
	{
		Helper::addAction('links', 'Links_Action');
		Helper::addPanel(3, "Links/Panel.php", _t("Links"), _t("Links"), 'administrator');
		$db = Typecho_Db::get();
		$charset = Helper::options()->charset == 'UTF-8' ? 'utf8' : 'gbk';
		$query = 'CREATE TABLE IF NOT EXISTS `'. $db->getPrefix() . 'links`' ." (
			`lid` int unsigned NOT NULL auto_increment PRIMARY KEY,
			`category` varchar(10) default 'external',
			`title` varchar(20) NOT NULL,
			`url` varchar(200) NOT NULL,
			`logo` varchar(200) default NULL,
			`description` varchar(200) default NULL,
			`created` int(10) default 0,
			`modified` int(10) default 0,
			`valid` tinyint(1) default 1
			) ENGINE=MyISAM DEFAULT CHARSET=". $charset;
		$db->query($query);
	}

	/** 禁用插件方法 */
	public static function deactivate()
	{
		Helper::removeAction('links');
		Helper::removePanel(3, 'Links/Panel.php');
		if (Helper::options()->plugin('Links')->drop)
		{
			$db = Typecho_Db::get();
			$db->query('DROP TABLE IF EXISTS '.$db->getPrefix().'links');
			return('插件已经禁用, 插件数据已经删除');
		}
		else
			return('插件已经禁用, 插件数据保留');
	}

	/** 插件配置方法 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$drop = new Typecho_Widget_Helper_Form_Element_Radio('drop', array(0 => _t('不刪除'), 1 => _t('刪除')), 0, _t('禁用时是否删除数据'), _t('选择在禁用插件的同时是否删除数据库中的插件数据内容'));
		$form->addInput($drop);
	}

	/** 个人用户的配置方法 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form)
	{
	}

	/**
	 * 插件模板输出方法
	 *
	 * @access public
	 * @return void
	 */
	public static function render()
	{
		echo 'This is the render method of Links.';
	}

	/**
	 * 根据分类名获取链接
	 *
	 * @access public
	 * @param String $category 分类标签名
	 * @param Bool $valid 链接有效性
	 * @return Array
	 */
	public static function getLinks($category=NULL, $valid=NULL)
	{
		$db = Typecho_Db::get();
		$select = $db->select()->from('table.links');
		if($category)
			$select->where('category = ?', $category);
		if($valid)
			$select->where('valid = ?', $valid);
		$rows = $db->fetchAll($select);
		return $rows;
	}
}
?>
