import React from 'react';
import { uploadTemporaryAvatar } from '../../api/avatar';
import { getText } from '../../utils/texts';
import { alertError, alertSuccess } from '../../utils/alerts';

const AvatarChangeButton = (props) => {
    const [uploading, setUploading] = React.useState(false);
    const inputFile = React.useRef(null)

    const onChangeFile = async (event) => {
        event.stopPropagation();
        event.preventDefault();
        var file = event.target.files[0];

        setUploading(true);

        if (file) {
            const result = await uploadTemporaryAvatar(file);

            setUploading(false);

            if (result?.status && result.status >= 200 && result.status < 300) {
                props.onChange();
                alertSuccess(getText('profile.change.avatar.success'));
            } else if (result?.status === 413) {
                alertError(getText('profile.change.avatar.fail.file.too.big'));
            } else {
                alertError(getText('profile.change.avatar.fail'));
            }
        }
        inputFile.current.value = null;
    }

    const onButtonClick = () => {
        inputFile.current.click();
    };

    const btnStyle = {
        cursor: uploading ? 'wait' : 'pointer',
    }
    const btnText = uploading ? getText('uploading') + ' ...' : getText('profile.change.avatar');

    return <>
        <input type='file' id='file' ref={inputFile} style={{ display: 'none' }} onChange={onChangeFile} />
        <button type="button" onClick={onButtonClick} className="btn btn-info btn-block" disabled={uploading} style={btnStyle}>{btnText}</button>
    </>
}

export default AvatarChangeButton;
