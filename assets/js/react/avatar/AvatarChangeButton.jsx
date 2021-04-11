import React from 'react';
import {uploadTemporaryAvatar} from '../../api/avatar'

const AvatarChangeButton = () => {
    const inputFile = React.useRef(null)

    const onChangeFile = async (event) => {
        event.stopPropagation();
        event.preventDefault();
        var file = event.target.files[0];
        console.log(file);

        const result = await uploadTemporaryAvatar(file);

        console.log(result);
    }

    const onButtonClick = () => {
       inputFile.current.click();
    };

    return <>
        <input type='file' id='file' ref={inputFile} style={{display: 'none'}} onChange={onChangeFile}/>
        <button type="button" onClick={onButtonClick} className="btn btn-info btn-block">Change Profile Picture</button>
    </>
}

export default AvatarChangeButton;