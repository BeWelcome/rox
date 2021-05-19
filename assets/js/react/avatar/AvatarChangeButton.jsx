import React from 'react';
import { persistAvatar, uploadTemporaryAvatar } from '../../api/avatar';
import { getText } from '../../utils/texts';
import { alertError, alertSuccess } from '../../utils/alerts';
import Popup from '../Popup';


const AvatarChangeButton = (props) => {
    const [uploading, setUploading] = React.useState(false);
    const [confirming, setConfirming] = React.useState(false);
    const [showPopup, setShowPopup] = React.useState(false);
    const inputFile = React.useRef(null)
    const basePictureUrl = window.globals.config.avatarUrl;

    const onChangeFile = async (event) => {
        event.stopPropagation();
        event.preventDefault();
        var file = event.target.files[0];

        setUploading(true);

        if (file) {
            const result = await uploadTemporaryAvatar(file);

            setUploading(false);

            if (result?.status && result.status >= 200 && result.status < 300) {
                setShowPopup(true);
            } else if (result?.status === 413) {
                alertError(getText('profile.change.avatar.fail.file.to.big'));
            } else {
                alertError(getText('profile.change.avatar.fail'));
            }
        }
        inputFile.current.value = null;
    }

    const onAvatarConfirm = async () => {
        setConfirming(true);
        const result = await persistAvatar();
        setConfirming(false);
        setShowPopup(false);
        if (result?.status && result.status >= 200 && result.status < 300) {
            props.onChange();
            alertSuccess(getText('profile.change.avatar.success'));
        } else {
            alertError(getText('profile.change.avatar.fail'));
        }
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
        <Popup modalId="avatarUpdate" show={showPopup} title={getText('profile.change.avatar.confirm')}>
            <div className="d-flex flex-column">
                <img src={`${basePictureUrl}/tmp`} className="m-5"/>
                <button type="button"  className="btn btn-primary" onClick={onAvatarConfirm} disabled={confirming}>{getText('save')}</button>
            </div>
        </Popup>
    </>
}

export default AvatarChangeButton;
