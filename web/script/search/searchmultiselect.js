function enableMultiSelect() {
    jQuery(".multiselect").multiselect({
        checkAllText: checkAllTextTranslation,
        uncheckAllText: uncheckAllTextTranslation,
        noneSelectedText: noneSelectedTextTranslation,
        selectedText: selectedTextTranslation
    });
}

jQuery(function () {
    enableMultiSelect();
});