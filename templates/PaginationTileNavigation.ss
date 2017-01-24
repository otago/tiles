<div class="widgettiles">
	<a class="widgettiles__prev" href="{$Link}tile?id=$Prev.ID">
		<% if $Prev %><span class="icon-ChevronLeft" aria-hidden="true"></span><% end_if %>
	</a>
	<a class="widgettiles__next" href="{$Link}tile?id=$Next.ID">
		<% if $Next %><span class="icon-ChevronRight" aria-hidden="true"></span><% end_if %>
	</a>
</div>
<div class="widgettiles__img">
	$CurrentItem.PageBanner.croppedImage(950, 534)
</div>
$CurrentItem.PageContent
