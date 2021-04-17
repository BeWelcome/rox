import React from 'react';
import {getText} from '../../utils/texts';

const AvatarPicture = (props) => {
    const config = window.globals.config;

    const useLightbox = config.avatarUseLightbox;
    const basePictureUrl = config.avatarUrl;
    const pictureUrl = useLightbox ? `${basePictureUrl}/original` : `/members/${config.username}`;
    const pictureTitle = getText('profile.picture.title');

    const imageStyle = {
        // We are adding changeCount here to make browser skip it's cache and refetch the picture
        backgroundImage: `url('${basePictureUrl}/500?${props.changeCount}')`,
    };

    return (
        <div className="avatar-box">
            <a className="avatar-box-inside" href={pictureUrl} data-toggle="lightbox" data-type="image" title={pictureTitle} style={imageStyle} />
        </div>
    )
}

export default AvatarPicture;