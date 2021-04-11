export const uploadTemporaryAvatar = async (file) => {
    var form = new FormData();
    form.append("file", file);

    return form;
}