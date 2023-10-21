import React from 'react';
import {getText} from '../../utils/texts';

const AvatarPicture = (props) => {
    const config = window.globals.config;

    const useLightbox = config.avatarUseLightbox;
    const basePictureUrl = config.avatarUrl;
    const pictureUrl = useLightbox ? `${basePictureUrl}/original` : `/members/avatar/${config.username}/500`;
    const pictureTitle = getText('profile.picture.title');

    const imageStyle = {
        // We are adding changeCount here to make browser skips its cache and re-fetches the picture
        backgroundImage: `url('${basePictureUrl}/original?${props.changeCount}')`,
    };

    return (
        <div className="u-w-full u-relative u-pb-[100%]">
            <div className="u-absolute u-left-0 u-top-0 u-w-full u-h-full">
                <a href={pictureUrl} title={pictureTitle}  data-toggle="lightbox" data-type="image">
                    <img className="u-rounded-8 u-w-full u-h-full u-object-cover js-profile-picture"
                         src={pictureUrl} alt={pictureTitle}/>
                </a>
            </div>
        </div>
    )
}

export default AvatarPicture;
