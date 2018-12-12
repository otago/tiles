<div class="tile tile-{$Width}x{$Height} tile__row-$Col tile__col-$Row">
<div class="tile__$CSSName <% if $Color %>$Color<% end_if %>">
	<ul class="tile__gallerytilecontainer">
	<% loop Slides %>
	<li class="imageContainer <% if $First %>first<% else %>mid<% end_if %><% if $VideoURL %> videotile<% end_if %>" <% if $First %><% else %>style="display:none"<% end_if %>>
		<% if $LinkURL %><a href="$LinkURL" target="_blank" <% if $VideoURL %>class="openintile"<% end_if %>><% end_if %>
		$Image.Fill(230, 430)
		<% if $LinkURL %></a><% end_if %>
	</li>
	<% end_loop %>
	</ul>
</div>
</div>
