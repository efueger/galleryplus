<?php
/**
 * @var $_ array
 */
/**
 * @var $l OC_L10N
 */
script(
	$_['appName'],
	[
		'app',
		'gallery',
		'galleryutility',
		'galleryconfig',
		'galleryinfobox',
		'galleryview',
		'breadcrumb',
		'galleryalbum',
		'galleryrow',
		'galleryimage',
		'thumbnail',
		'vendor/modified-eventsource-polyfill/eventsource-polyfill',
		'eventsource',
		'vendor/marked/marked.min',
		'vendor/bigshot/bigshot-compressed',
		'slideshow',
		'slideshowcontrols',
		'slideshowzoomablepreview',
		'vendor/image-scale/image-scale.min'
	]
);
style(
	$_['appName'],
	[
		'styles',
		'mobile',
		'gallerybutton',
		'github-markdown',
		'slideshow'
	]
);
?>
<div id="controls">
	<div id='breadcrumbs'></div>
	<!-- toggle for opening shared picture view as file list -->
	<div id="openAsFileListButton" class="button">
		<img class="svg" src="<?php print_unescaped(
			image_path('core', 'actions/toggle-filelist.svg')
		); ?>" alt="<?php p($l->t('File list')); ?>"/>
	</div>
	<div id="sort-name-button" class="button left-sort-button">
		<img class="svg" src="<?php print_unescaped(
			image_path($_['appName'], 'nameasc.svg')
		); ?>" alt="<?php p($l->t('Sort by name')); ?>"/>
	</div>
	<div id="sort-date-button" class="button right-sort-button">
		<img class="svg" src="<?php print_unescaped(
			image_path($_['appName'], 'dateasc.svg')
		); ?>" alt="<?php p($l->t('Sort by date')); ?>"/>
	</div>
	<span class="right">
		<div id="album-info-button" class="button">
			<span class="ribbon black"></span>
			<img class="svg" src="<?php print_unescaped(
				image_path('core', 'actions/info.svg')
			); ?>" alt="<?php p($l->t('Album information')); ?>"/>
		</div>
		<div class="album-info-content markdown-body"></div>
	</span>
	<span class="right">
		<div id="share-button" class="button">
			<img class="svg" src="<?php print_unescaped(
				image_path('core', 'actions/share.svg')
			); ?>" alt="<?php p($l->t("Share")); ?>"/>
		</div>
		<a class="share" data-item-type="folder" data-item=""
		   title="<?php p($l->t("Share")); ?>"
		   data-possible-permissions="31"></a>
	</span>
</div>
<div id="gallery" class="hascontrols"></div>
<div id="emptycontent" class="hidden"></div>
<input type="hidden" name="allowShareWithLink" id="allowShareWithLink" value="yes"/>
