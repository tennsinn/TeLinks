# TeLinks

Typecho链接管理插件

## Features

- 链接的数据库存储和后台管理
- 存储链接的点击计数

## Instructions

### 链接管理

启用后在后台*管理*菜单下*Links*页面进行管理，编辑方法与Typecho标签一致。

数据存储于*table.links*数据表中，计数值位于*clicksNum*字段，最后点击时间位于*clicked*字段。

### 链接获取

使用getLinks函数获取存储的链接，返回为数组。  
支持按分类，有效性筛选，和按固定字段排序。

	Links_Plugin::getLinks($category=NULL, $valid=NULL, $orderby=NULL)

> `$category` 分类标签名
> `$valid` 链接有效性标记
> `$orderby` 排序字段

### 点击计数统计

计数通过Action实现，即访问click action并以传入的lid值进行统计，后跳转到存储的链接。

主题输出统计链接示意：

```php
<?php $links = Links_Plugin::getLinks('exchange'); ?>
<?php foreach($links as $link) : ?>
	<a href="<?php $this->options->index('/action/links?do=click&lid='.$link['lid']); ?>" title="<?=$link['description']?>" target="_blank"><?=$link['title']?></a>
<?php endforeach; ?>
```
