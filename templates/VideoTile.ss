<div class="tile">
<div class="tile-$Size $CSSName">
    <% if $ViewOptions == 'OpenInNewTab' %>
        <a href='$SourceURL' target='_blank'>
            <div class="videocontainer">
                $Thumb
            </div>
        </a>
    <% else_if $ViewOptions == 'OpenInTile' %>
        <a href='$EmbedURL' class="openintile">
            <div class="videocontainer">
                $Thumb
            </div>
        </a>
    <% else_if $ViewOptions == 'OpenInLightbox' %>
        <a href='$EmbedURL' class="openinlightbox">
            <div class="videocontainer">
                $Thumb
            </div>
        </a>
    <% end_if %>
</div>
</div>