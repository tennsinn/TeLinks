<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>

<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="row typecho-page-main manage-links">
			<div class="col-mb-12 col-tb-8" role="main">
				<form method="post" name="manage_links" class="operate-form">
				<div class="typecho-list-operate clearfix">
					<div class="operate">
						<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
						<div class="btn-group btn-drop">
							<button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
							<ul class="dropdown-menu">
								<li><a lang="<?php _e('你确认要删除这些链接吗?'); ?>" href="<?php $options->index('/action/links?do=delete'); ?>"><?php _e('删除'); ?></a></li>
							</ul>
						</div>
					</div>
				</div>
				<ul class="typecho-list-notable tag-list clearfix">
					<?php $links = Links_Plugin::getLinks(); ?>
					<?php if($links) : ?>
					<?php foreach($links as $link) : ?>
					<li id="<?=$link['lid']?>">
					<input type="checkbox" value="<?=$link['lid']?>" name="lid[]"/>
					<span rel="<?php echo $request->makeUriByRequest('lid=' . $link['lid']); ?>"><?=$link['title']?></span>
					<a class="tag-edit-link" href="<?php echo $request->makeUriByRequest('lid=' . $link['lid']); ?>"><i class="i-edit"></i></a>
					</li>
					<?php endforeach; ?>
					<?php else: ?>
					<h6 class="typecho-list-table-title"><?php _e('没有任何链接'); ?></h6>
					<?php endif; ?>
				</ul>
				<input type="hidden" name="do" value="delete" />
				</form>
			</div>
			<div class="col-mb-12 col-tb-4" role="form">
			<?php Typecho_Widget::widget('Links_Action')->form()->render(); ?>
			</div>
		</div>
	</div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
(function () {
	$(document).ready(function () {
		$('.typecho-list-notable').tableSelectable({
			checkEl     :   'input[type=checkbox]',
			rowEl       :   'li',
			selectAllEl :   '.typecho-table-select-all',
			actionEl    :   '.dropdown-menu a'
		});

		$('.btn-drop').dropdownMenu({
			btnEl       :   '.dropdown-toggle',
			menuEl      :   '.dropdown-menu'
		});
	});
})();
</script>

<?php
include 'footer.php';
?>
