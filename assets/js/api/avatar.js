export const uploadTemporaryAvatar = async (file) => {
    var form = new FormData();
    form.append("avatar", file);

    const endpoint = `${window.globals.baseUrl}/members/uploadavatar`;

    const response = fetch(endpoint, { method: 'POST', body: form });

    return response;
}
