<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Links_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $_options;
	private $_security;
	private $_db;

	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_options = Helper::options();
		$this->_security = Helper::security();
		$this->_db = Typecho_Db::get();
	}

	/**
	 * 入口函数
	 *
	 * @access public
	 * @return void
	 */
	public function action()
	{
		$this->on($this->request->is('do=click'))->clickLink();
		$this->_security->protect();
		Typecho_Widget::widget('Widget_User')->pass('administrator');
		$this->on($this->request->is('do=insert'))->insertLink();
		$this->on($this->request->is('do=update'))->updateLink();
		$this->on($this->request->is('do=delete'))->deleteLink();
	}

	/**
	 * 链接编辑表格
	 *
	 * @access public
	 * @param string $action 表单动作
	 * @return Typecho_Widget_Helper_Form
	 */
	public function form($action=NULL)
	{
		$form = new Typecho_Widget_Helper_Form($this->_security->getIndex('/action/links'), Typecho_Widget_Helper_Form::POST_METHOD);

		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);

		$lid = new Typecho_Widget_Helper_Form_Element_Hidden('lid');
		$form->addInput($lid);

		$category = new Typecho_Widget_Helper_Form_Element_Text('category', NULL, NULL, _t('链接分类'), _t('这是链接的分类标签。'));
		$form->addInput($category);

		$title = new Typecho_Widget_Helper_Form_Element_Text('title', NULL, NULL, _t('链接标题 *'), _t('这是链接站点的标题。'));
		$form->addInput($title);

		$url = new Typecho_Widget_Helper_Form_Element_Text('url', NULL, NULL, _t('链接地址 *'), _t('这是链接站点的地址。'));
		$form->addInput($url);

		$logo = new Typecho_Widget_Helper_Form_Element_Text('logo', NULL, NULL, _t('链接Logo'), _t('这是链接Logo的地址。'));
		$form->addInput($logo);

		$description = new Typecho_Widget_Helper_Form_Element_Textarea('description', NULL, NULL, _t('链接说明'), _t('这是链接站点的说明。'));
		$form->addInput($description);

		$valid = new Typecho_Widget_Helper_Form_Element_Radio('valid', array(0=>'失效', 1=>'有效'), 1, _t('链接有效性'), _t('这是链接的有效性标志。'));
		$form->addInput($valid);

		$submit = new Typecho_Widget_Helper_Form_Element_Submit();
		$submit->input->setAttribute('class', 'btn primary');
		$form->addItem($submit);

		if (isset($this->request->lid) && 'insert' != $action)
		{
			$link = $this->_db->fetchRow($this->_db->select()->from('table.links')->where('lid = ?', $this->request->lid)->limit(1));
			if (!$link)
				$this->response->goBack();

			$do->value('update');
			$lid->value($link['lid']);
			$category->value($link['category']);
			$title->value($link['title']);
			$url->value($link['url']);
			$logo->value($link['logo']);
			$description->value($link['description']);
			$valid->value($link['valid']);
			$submit->value(_t('编辑链接'));
			$_action = 'update';
		}
		else
		{
			$do->value('insert');
			$submit->value(_t('增加链接'));
			$_action = 'insert';
		}

		if (empty($action))
			$action = $_action;

		if ('insert' == $action || 'update' == $action)
		{
			$title->addRule('required', _t('必须填写站点标题'));
			$title->addRule('xssCheck', _t('请不要站点标题中使用特殊字符'));
			$url->addRule('required', _t('必须填写站点链接地址'));
			$url->addRule('url', _t('请输入正确的链接地址'));
			$logo->addRule('url', _t('请输入正确的链接地址'));
		}
		if ('update' == $action)
		{
			$lid->addRule('required', _t('标签主键不存在'));
		}

		return $form;
	}

	/**
	 * 插入链接
	 *
	 * @access public
	 * @return void
	 */
	public function insertLink()
	{
		if ($this->form('insert')->validate())
			$this->response->goBack();

		/** 取出数据 */
		$link = $this->request->from('category', 'title', 'url', 'logo', 'description', 'valid');
		$link['created'] = Typecho_Date::gmtTime();
		$link['modified'] = Typecho_Date::gmtTime();

		/** 插入数据 */
		$link['lid'] = $this->_db->query($this->_db->insert('table.links')->rows($link));

		/** 设置高亮和提示信息 */
		$this->widget('Widget_Notice')->highlight($link['lid']);
		$this->widget('Widget_Notice')->set(_t('链接%s已经被增加', $link['title']), 'success');

		/** 转向原页 */
		$this->response->goBack();
	}

	/**
	 * 更新链接
	 *
	 * @access public
	 * @return void
	 */
	public function updateLink()
	{
		if ($this->form('update')->validate())
			$this->response->goBack();

		/** 取出数据 */
		$link = $this->request->from('lid', 'category', 'title', 'url', 'logo', 'description', 'valid');
		$link['modified'] = Typecho_Date::gmtTime();

		/** 更新数据 */
		$this->_db->query($this->_db->update('table.links')->rows($link)->where('lid = ?', $this->request->filter('int')->lid));

		/** 设置高亮和提示信息 */
		$this->widget('Widget_Notice')->highlight($this->request->filter('int')->lid);
		$this->widget('Widget_Notice')->set(_t('链接%s已经被更新', $link['title']), 'success');

		/** 转向原页 */
		$this->response->goBack();
	}

	/**
	 * 删除链接
	 *
	 * @access public
	 * @return void
	 */
	public function deleteLink()
	{
		$links = $this->request->filter('int')->getArray('lid');
		$deleteCount = 0;

		if ($links && is_array($links))
		{
			foreach ($links as $link)
			{
				$this->_db->query($this->_db->delete('table.links')->where('lid = ?', $link));
				$deleteCount ++;
			}
		}

		/** 设置提示信息 */
		$this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('链接已经删除') : _t('没有链接被删除'), $deleteCount > 0 ? 'success' : 'notice');

		/** 转向链接管理页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Links%2FPanel.php', $this->_options->adminUrl));
	}

	/**
	 * 点击链接
	 *
	 * @access public
	 * @return void
	 */
	public function clickLink()
	{
		$lid = $this->request->filter('int')->get('lid');
		$link = $this->_db->fetchRow($this->_db->select()->from('table.links')->where('lid = ?', $lid));
		if($link)
		{
			$update = $this->_db->update('table.links')->where('lid = ?', $lid);
			$update->expression('clicksNum', 'clicksNum + 1');
			$update->expression('clicked', Typecho_Date::gmtTime());
			$this->_db->query($update);
			$this->response->redirect($link['url']);
		}
		else
			$this->response->goBack();
	}
}
