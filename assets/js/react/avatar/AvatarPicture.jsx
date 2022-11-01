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
        <div className="u-w-full u-relative u-pb-[100%]">
            <a className="u-absolute u-left-0 u-top-0" href={pictureUrl} data-toggle="lightbox" data-type="image" title={pictureTitle}>
                <img className="u-rounded-16 u-max-w-full" src={pictureUrl} alt={pictureTitle}/>
            </a>
        </div>
    )
}

export default AvatarPicture;
