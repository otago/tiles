<div class="tile">
<div class="tile-$Size $CSSName">
	<ul>
	<% loop Images %>
	<li class="imageContainer <% if $First %>first<% else %>mid<% end_if %><% if $VideoURL %> videotile<% end_if %>" <% if $First %><% else %>style="display:none"<% end_if %>>
		<% if $LinkURL %><a href="$LinkURL" target="_blank" <% if $VideoURL %>class="openintile"<% end_if %>><% end_if %>
		
        <% if $Up.Size = 3x2 %>
            $Me.croppedImage(710, 430)
        <% end_if %>
        <% if $Up.Size = 2x2 %>
            $Me.croppedImage(469, 430)
        <% end_if %>
        <% if $Up.Size = 1x1 %>
            $Me.croppedImage(230, 210)
        <% end_if %>
		<% if $LinkURL %></a><% end_if %>
	</li>
	<% end_loop %>
	</ul>
</div>
</div>