<% if ShowTile %>
<div class="tile">
	<div class="tile__$CSSName tile__size$getSize <% if $Color %>$Color<% end_if %>">
        <span class="$Type icon" aria-hidden="true"></span>
        $Content
    </div>
</div>
<% end_if %>