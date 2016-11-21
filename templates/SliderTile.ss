<div class="tile">
    <div class="tile-$Size $CSSName">
        <ul>
            <% loop ChildTiles %>
                <% if not Disabled %>
                <li>
                    $Me
                </li>
                <% end_if %>
            <% end_loop %>
        </ul>
    </div>
</div>