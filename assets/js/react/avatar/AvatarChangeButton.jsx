import React from 'react';
import {uploadTemporaryAvatar} from '../../api/avatar'
import {getText} from '../../utils/texts';
import {alertError, alertSuccess} from '../../utils/alerts';

const AvatarChangeButton = () => {
    const inputFile = React.useRef(null)

    const onChangeFile = async (event) => {
        event.stopPropagation();
        event.preventDefault();
        var file = event.target.files[0];

        if (file) {
            const result = await uploadTemporaryAvatar(file);

            if (result?.status && result.status >= 200 && result.status < 300) {
                alertSuccess(getText('profile.change.avatar.success'));
            } else if (result?.status === 413) {
                alertError(getText('profile.change.avatar.fail.file.to.big'));
            } else {
                alertError(getText('profile.change.avatar.fail'));
            }
        }
        inputFile.current.value = null;
    }

    const onButtonClick = () => {
       inputFile.current.click();
    };

    return <>
        <input type='file' id='file' ref={inputFile} style={{display: 'none'}} onChange={onChangeFile}/>
        <button type="button" onClick={onButtonClick} className="btn btn-info btn-block">{getText('profile.change.avatar')}</button>
    </>
}

export default AvatarChangeButton;
