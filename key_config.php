<input type="hidden" id="azp" name="azp" value="<?php echo $azpInfo->id; ?>" />
<input type="hidden" id="keyid" name="azp" value="<?php echo $keyInfo->id; ?>" />
<div id="settings">
    <div>
        <ul>
            <li><input id="kid" name="kid" type="text" placeholder="Key Id (as provided by Authority)" value="<?php echo $keyInfo->kid; ?>"></li>
            <li><input id="jku" name="jku" type="text" placeholder="jku (as provided by the authority)" value="<?php echo $keyInfo->jku;?>"></li>
            <li><textarea id="key" name="key"><?php echo $keyInfo->key;?></textarea></li>
        </ul>
        <div>
            <input type="submit" id="storekey" name="storekey" value="Update Key">
        </div>
    </div>
</div>
