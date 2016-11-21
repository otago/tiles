<div class="tile">
    <div class="tile-$Size $CSSName">
        <% if $Size = 2x2 %>
            $Image.croppedImage(469, 430)
        <% end_if %>
        <% if $Size = 1x1 %>
            $Image.croppedImage(230, 210)
        <% end_if %>
    </div>
</div>