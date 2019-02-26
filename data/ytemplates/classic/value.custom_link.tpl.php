<div class="<?php echo $this->getHTMLClass() ?>" id="<?php echo $this->getHTMLId() ?>">
    <label class="text <?php echo $this->getWarningClass() ?>" for="<?php echo $this->getFieldId() ?>" ><?php echo $this->getLabel() ?></label>
    <div class="rex-widget">
        <div class="rex-widget-link">
            <p class="rex-widget-field">
                <input type="hidden" name="<?php echo $this->getFieldName() ?>" id="LINK_<?php echo $counter ?>" value="<?php echo $this->getValue() ?>" />
                <input type="text" size="30" name="LINK_<?php echo $counter ?>_NAME" value="<?php echo htmlspecialchars($linkName) ?>" id="LINK_<?php echo $counter ?>_NAME" readonly="readonly" />
            </p>

            <p class="rex-widget-icons rex-widget-1col">
                <span class="rex-widget-column rex-widget-column-first">
                    <a href="#" class="rex-icon-file-open" onclick="openLinkMap('LINK_<?php echo $counter ?>', '&clang=0&category_id=1');return false;" title="Link auswählen" tabindex="21"></a>
                    <a href="#" class="rex-icon-file-delete" onclick="deleteREXLink(<?php echo $counter ?>);return false;" title="Ausgewählten Link löschen" tabindex="22"></a>
                </span>
            </p>
        </div>
    </div>
    <div class="rex-clearer"></div>
</div>
