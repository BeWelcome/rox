export const uploadTemporaryAvatar = async (file) => {
    var form = new FormData();
    form.append("avatar", file);

    const endpoint = `${window.globals.baseUrl}/members/uploadavatar?tmp=true`;

    return await fetch(endpoint, { method: 'POST', body: form });
}

export const persistAvatar = async () => {

    const endpoint = `${window.globals.baseUrl}/members/persisttmpavatar`;

    return await fetch(endpoint, { method: 'POST' });
}
