<div class="gridTileContainer">
	<div class="gridster">
		<ul style="width:100%" data-maxcols="$MaxColumns">
			<% loop Options %>
			<li data-id="$ID" data-row="$Row" data-col="$Col" data-sizex="$Sizex" data-sizey="$Sizey">
				<header>
                    <span class="ui-button-icon-primary ui-icon btn-icon-pencil"></span> 
                    <span class="ui-button-icon-primary ui-icon btn-icon-delete"></span>
					
					<% if isVersioned %>
						<span class="ui-button-icon-primary btn-icon-version">$Version</span>
					<% end_if %>
                     |||
				</header>
				<div class="tileFieldContainer" style="background-color: $PreviewColor">
					<strong>$RecordClassName</strong><br/>
					$Preview
				</div>
			</li>
			<% end_loop %>
		</ul>
		<input $AttributesHTML />
		
		<div class="field dropdown">
			<div class="dropdowncontainer">
				<select>
					<option value="" selected>Select new tile type</option>
					<% loop TileTypes %>
						<option value="$Name">$NiceName</option>
					<% end_loop %>
				</select> 
			</div>
			<button>
				<span class="ui-button-icon-primary ui-icon btn-icon-add"></span>
				Add new tile
			</button>
			<span class="gridsterloader"></span>
		</div>
	</div>
</div>