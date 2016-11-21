<% if ShowTile %>
<div class="tile">
    <div class="tile-$Size $CSSName <% if $Color %>$Color<% end_if %>">
        <span class="$Type icon" aria-hidden="true"></span>
        $Content
    </div>
</div>
<% end_if %>