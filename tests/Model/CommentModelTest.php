<?php

namespace App\Tests\Model;

use App\Doctrine\CommentRelationsType;
use App\Entity\Comment;
use App\Model\CommentModel;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class CommentModelTest extends TestCase
{
    private CommentModel $commentModel;

    public function setUp(): void
    {
        $em = $this->createStub(EntityManager::class);
        $this->commentModel = new CommentModel($em);
    }

    public function testNewExperienceOneNewRelation()
    {
        $original = new Comment();
        $original
            ->setRelations($this->buildRelations([
                CommentRelationsType::WAS_GUEST,
            ]))
            ->setTextFree('Lorem ipsum.')
        ;
        $updated = clone $original;
        $updated
            ->setRelations(
                $this->addRelations($updated->getRelations(), [
                    CommentRelationsType::WAS_HOST,
                ])
            )
            ->setTextFree('Lorem ipsum.')
        ;

        $this->assertTrue($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceRelation()
    {
        $original = new Comment();
        $original->setRelations($this->buildRelations([
            CommentRelationsType::WAS_GUEST,
        ]))->setTextFree('Lorem ipsum.');
        $updated = clone $original;

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNewExperienceTextAdded()
    {
        $original = new Comment();
        $original->setTextFree('Lorem ipsum.');
        $updated = new Comment();
        $updated->setTextFree('Lorem ipsum. Lorem ipsum.');

        $this->assertTrue($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceNoTextChanged()
    {
        $original = new Comment();
        $original->setTextFree('Lorem ipsum.');
        $updated = new Comment();
        $updated->setTextFree('Lorem ipsum.');

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceTypoFixed()
    {
        $original = new Comment();
        $original->setTextFree('Lorem pisum. Lorem ipsum.');
        $updated = new Comment();
        $updated->setTextFree('Lorem ipsum. Lorem ipsum.');

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceNegationFixed()
    {
        $original = new Comment();
        $original->setTextFree('I can stand the rain.');
        $updated = new Comment();
        $updated->setTextFree('I can\'t stand the rain.');

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceRemovedLineBreaks()
    {
        $original = new Comment();
        $original->setTextFree(
            'First line.' .
            PHP_EOL .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.' .
            PHP_EOL
        );
        $updated = new Comment();
        $updated->setTextFree(
            'First line.' .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.'
        );

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceAddedLineBreaks()
    {
        $original = new Comment();
        $original->setTextFree(
            'First line.' .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.'
        );
        $updated = new Comment();
        $updated->setTextFree(
            'First line.' .
            PHP_EOL .
            PHP_EOL .
            'Second line.' .
            PHP_EOL .
            'Third line.' .
            PHP_EOL
        );

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNoNewExperienceSmallTextChanges()
    {
        $original = new Comment();
        $original->setTextFree(
            'Olli is a very charming and friendly guest, who is eager to discover the world!' .
            PHP_EOL .
            PHP_EOL .
            'All the best for your journey(s)! Take care and stay healthy!'
        );
        $updated = new Comment();
        $updated->setTextFree(
            'Methusalem is a very charming and friendly guest, who is eager to discover the world!' .
            PHP_EOL .
            'All the best for your journey! Take care and stay healthy!'
        );

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testNewExperienceTextChanges()
    {
        $original = new Comment();
        $original->setTextFree('I can stand the rain but not the snow.');
        $updated = new Comment();
        $updated->setTextFree('I like the snow can stand the rain.');

        $this->assertTrue($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testLongTextWithLotsOfUpdatesIsANewExperience()
    {
        $original = new Comment();
        $original->setTextFree(<<<COMMENT
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sodales scelerisque aliquam. Curabitur viverra quam ornare ligula tempus vehicula. Pellentesque vehicula eros at urna faucibus luctus. Donec et posuere ipsum. Nulla massa nunc, pulvinar sit amet sodales at, euismod volutpat sem. Nulla pharetra velit nibh, sollicitudin vulputate enim vestibulum vitae. Vestibulum lacinia, urna varius aliquet aliquet, nisi metus posuere urna, iaculis interdum tellus felis nec neque. Morbi sed semper orci, eu accumsan libero. Donec ullamcorper libero vel eleifend blandit. Proin id mauris libero. Morbi egestas convallis condimentum. Aliquam vitae lectus sapien. Donec id accumsan arcu. Nulla porta, nisl ac egestas hendrerit, purus metus tincidunt ex, nec maximus sapien ex condimentum turpis.

        Sed faucibus magna et tellus tempor pulvinar. Sed tristique urna quam, sed tincidunt risus scelerisque vel. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Vestibulum elementum hendrerit tortor non tempus. Curabitur cursus lacus maximus, varius nibh ut, mollis ex. Nullam eget neque at arcu laoreet vestibulum sed in velit. Etiam nec nunc nec ligula suscipit ornare.

        In fringilla dapibus mattis. Sed sed sapien lacus. Suspendisse nec libero vitae quam ullamcorper interdum at a magna. Curabitur blandit ipsum at dui ultricies vulputate. Morbi accumsan justo nulla, eu semper mauris volutpat in. Maecenas vestibulum lacus sit amet elit tristique, quis rutrum arcu pellentesque. Maecenas tincidunt convallis diam.

        Proin laoreet ligula a justo rutrum, eu rhoncus dui euismod. Nunc consectetur dolor sem, tincidunt finibus nisl rhoncus tristique. Mauris tincidunt libero sit amet mauris congue, ut elementum libero tristique. Nullam ut sem cursus, condimentum libero condimentum, accumsan dolor. Duis bibendum nulla ut pharetra eleifend. Etiam orci leo, rhoncus non ultrices sit amet, fermentum non arcu. Aenean bibendum dui massa, quis vulputate sapien pretium nec. Pellentesque quis justo laoreet, varius massa ut, sagittis nisl. Sed id ligula ac mauris aliquam tempus. Etiam libero nulla, volutpat efficitur interdum sed, accumsan sodales ex. Nam pretium sem vel auctor vulputate. Praesent in nibh lacinia, malesuada dolor sit amet, commodo turpis. Aenean ultrices ex facilisis, facilisis augue vitae, suscipit ex. Fusce viverra ex metus. Nam feugiat mi quis ipsum aliquet pretium.

        Nam vestibulum rutrum convallis. Mauris et finibus nunc. Cras dictum aliquet varius. Nam hendrerit convallis lorem, ut gravida erat auctor ac. Duis ultricies quis sem nec laoreet. Sed suscipit est nec odio porta consequat. Donec mauris purus, pretium eget massa a, accumsan fermentum tortor.

        Sed et convallis dolor. Praesent pellentesque egestas turpis sit amet interdum. Sed ac lectus auctor, sollicitudin elit a, lacinia augue. Aenean et vehicula ex. Nulla hendrerit quis urna eu vestibulum. Integer vitae velit luctus, hendrerit magna vitae, pretium ligula. Morbi vel pulvinar lorem, ac rhoncus sem. In non gravida dui, id commodo ante. Morbi pharetra, orci id tristique dapibus, lectus lorem maximus neque, vitae gravida felis libero vitae felis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Ut ac cursus ligula. Proin tempus sem lorem, at vestibulum ante eleifend vitae.

        Aliquam non elit egestas, ultricies felis euismod, pulvinar nibh. Ut porta arcu vitae est cursus consectetur. Nulla consequat quam sed lorem tempor ultrices. Pellentesque fringilla, magna et euismod lacinia, erat leo fermentum dui, sed sagittis augue neque quis lectus. Sed dignissim laoreet lorem ac porttitor. Etiam non euismod tellus, nec finibus purus. Aliquam vitae porttitor ligula, quis aliquet risus. Sed tempor fermentum dapibus. Fusce quis tellus tincidunt, sollicitudin leo non, ultrices sapien. Nullam id lectus turpis. Integer tempor cursus aliquam. Fusce sed dapibus massa, eget condimentum enim. Duis eu pretium nulla, et aliquam nisl. Curabitur ultricies, arcu sit amet venenatis finibus, tortor quam luctus ex, sit amet hendrerit ante lacus sit amet sem.

        Donec fringilla vehicula ex quis tincidunt. In eu posuere justo. Nulla ut odio lacus. Nam aliquam sollicitudin feugiat. Pellentesque posuere quis quam in malesuada. Vestibulum consequat ante et vestibulum viverra. Nam auctor tristique mi at aliquam. Suspendisse potenti. Curabitur efficitur augue vitae pellentesque ultrices. Sed efficitur lobortis tempor. Ut sollicitudin dolor urna, sit amet imperdiet nisi viverra eget. Nullam nec venenatis ipsum, vel dapibus sapien. Ut a leo consectetur, scelerisque nulla sagittis, finibus sem.

        Duis condimentum egestas nibh, at viverra diam sagittis sed. Curabitur sit amet nisi elementum, consequat justo eget, pulvinar leo. Vivamus suscipit interdum mi et tempor. Aenean at ante nec ante venenatis aliquam eu at diam. Fusce at augue maximus, auctor eros nec, lobortis lorem. Integer venenatis justo varius enim porta volutpat a a augue. Integer eu risus ut turpis vulputate scelerisque at non leo. Phasellus fringilla cursus condimentum. Phasellus at bibendum erat. Phasellus at arcu id dolor volutpat auctor. Quisque ultricies nulla ac erat pulvinar faucibus.

        Donec elementum bibendum tellus eget tincidunt. Aenean congue tempus ultricies. Suspendisse commodo leo risus. Donec enim nunc, tempus et convallis ac, malesuada nec magna. Quisque tellus nibh, sollicitudin eu justo ut, dignissim bibendum lorem. Cras ac ex et purus commodo faucibus. In hac habitasse platea dictumst. Maecenas sodales porta arcu vel faucibus. Aliquam dignissim sodales semper. Sed sed ultrices purus, nec faucibus elit. Phasellus eget purus dolor.
        COMMENT);

        $updated = new Comment();
        $updated->setTextFree(<<<COMMENT
        Donec metus neque, bibendum id sem a, iaculis ullamcorper leo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Vestibulum id magna sollicitudin nibh eleifend fermentum ac sed ligula. Fusce luctus, augue quis sollicitudin varius, lorem nisi ullamcorper sem, nec eleifend ligula nulla ac ante. Maecenas et eleifend nisl. Praesent ac sagittis neque. Morbi bibendum justo in ligula auctor, nec congue lectus tempor. Nulla a sollicitudin massa. Ut eu nibh quis quam feugiat sollicitudin nec eget turpis. Phasellus enim mi, blandit a aliquet fringilla, fringilla facilisis felis. Nullam lobortis lorem nunc, quis laoreet sapien condimentum sit amet. Morbi id tincidunt nisi. Maecenas nec vulputate nulla, consequat lobortis massa. Nulla congue maximus est, a interdum diam placerat a.

        Vivamus libero mauris, sollicitudin in enim sollicitudin, ultrices molestie diam. Ut eget suscipit nisi, quis pretium mi. Aliquam ut augue mattis mi placerat sagittis. Mauris non fringilla orci. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec ornare velit sed ligula cursus, non euismod lacus fringilla. In ac mi sed tortor molestie commodo et ac eros. Sed sagittis sem turpis, in tincidunt arcu fringilla quis.

        Morbi egestas at magna eget dignissim. Curabitur sodales pharetra justo, vitae efficitur velit dapibus nec. Cras fermentum augue sed scelerisque efficitur. Etiam laoreet vestibulum dolor non lacinia. In tempor sem mi, sed vulputate ipsum pellentesque non. Nullam blandit, leo ut porttitor pharetra, enim sem fermentum elit, at mollis sapien metus ac eros. Suspendisse porta nisl felis, eget varius enim consequat ullamcorper.

        Fusce tincidunt magna sit amet odio semper semper. Aliquam facilisis nulla condimentum purus ultrices, tincidunt laoreet risus malesuada. Interdum et malesuada fames ac ante ipsum primis in faucibus. Ut id cursus mauris. Donec in tincidunt eros. Donec at ante velit. Praesent suscipit imperdiet rutrum. Sed feugiat magna nisl, rutrum cursus massa aliquam vitae. Nulla sit amet elit sed arcu auctor semper a in felis. Mauris porttitor a lorem ac pulvinar. Sed sed ex ac turpis porta varius. Phasellus convallis vulputate eleifend.

        Aliquam ac nisi lorem. Fusce convallis quam ut commodo cursus. Quisque tempus tincidunt arcu et fermentum. Integer nec ullamcorper lorem. Donec leo justo, suscipit et interdum in, congue eu purus. Aenean non massa vitae lectus interdum sollicitudin eget bibendum quam. Vestibulum malesuada, turpis quis luctus blandit, neque tortor congue metus, vitae pharetra lacus felis vel enim. Proin a ex sed sapien finibus tincidunt. Maecenas posuere ut metus eget imperdiet. Integer pulvinar diam nec molestie hendrerit. Suspendisse arcu felis, maximus eget suscipit eget, dapibus auctor libero.

        Phasellus elementum risus vel leo cursus, sit amet pretium ipsum placerat. Suspendisse sit amet luctus lectus. Vestibulum eros odio, ultrices sed elementum nec, placerat quis sapien. Fusce placerat nisi non quam interdum efficitur. Pellentesque vitae tincidunt metus. Nulla sodales accumsan accumsan. Sed faucibus, sapien ac porttitor porttitor, metus mi pharetra nisl, eget efficitur quam erat et dui. Sed erat enim, consectetur sit amet justo elementum, sodales euismod eros. Morbi vitae nulla auctor, cursus eros in, semper ipsum. Cras convallis ornare ullamcorper. Phasellus nec neque quam. Nam commodo vitae quam nec porttitor. Pellentesque nec suscipit quam, non finibus urna. Sed vitae volutpat augue.

        Curabitur vitae euismod erat, ut interdum ante. Sed lectus diam, pellentesque sed augue fermentum, bibendum interdum mi. Donec at nibh ut tortor aliquet ullamcorper vitae nec velit. Nam viverra sem tortor, ut placerat orci mollis id. Duis facilisis eget est nec pellentesque. Praesent mattis arcu ac sem eleifend congue. Integer porta metus iaculis, cursus nulla a, ultrices nunc. Morbi lobortis id lectus nec malesuada. In vitae arcu vel dui dapibus euismod. Integer in viverra nisi, in posuere orci. Fusce nulla tortor, fringilla a convallis ut, cursus at leo. Ut congue arcu ut magna accumsan, id dapibus tortor condimentum. Proin efficitur lectus sed metus feugiat euismod. Donec scelerisque leo ipsum, sed hendrerit ante iaculis lacinia.

        Maecenas dictum purus a orci porta, ac convallis dui aliquam. Curabitur sit amet blandit sem. Sed eleifend lectus nisi, nec rutrum erat eleifend suscipit. Nulla consequat felis vel nibh fermentum cursus. Quisque vel leo ultrices, iaculis neque at, facilisis nisl. Sed sit amet nisi metus. Aenean pretium varius interdum. Aenean ultrices dui non cursus lacinia. Phasellus lacinia nisi ac diam commodo convallis. Nunc lacinia libero tincidunt leo auctor porttitor.

        Nulla nibh nunc, scelerisque id purus a, bibendum dapibus tellus. Phasellus dictum elit a est tincidunt pulvinar. Maecenas et rutrum lorem. Vestibulum libero tellus, aliquet at convallis in, pretium nec ligula. Ut tempus eleifend luctus. Donec egestas diam sit amet finibus consectetur. Nam metus lorem, mollis in rutrum quis, mollis et leo. Cras malesuada, tortor vitae condimentum ultricies, augue tortor pretium urna, sed consectetur est lacus quis odio. Nullam tempus sed massa fermentum faucibus. Sed vel lacus tristique, porta erat eu, rhoncus ante. Donec nulla quam, lacinia eget porttitor ac, bibendum quis urna. Duis vitae nibh faucibus sapien bibendum ultricies. Quisque mollis lectus arcu, id laoreet tortor semper vel.

        Vivamus eu ornare arcu. Etiam blandit nunc aliquam placerat cursus. Praesent aliquam arcu vitae dolor sodales viverra. Duis orci velit, vulputate vel volutpat sodales, ultricies quis dui. Nunc risus urna, feugiat finibus venenatis ut, posuere a sem. Maecenas in faucibus ex. Pellentesque euismod, magna sit amet iaculis venenatis, mauris lacus tempor enim, a rutrum erat dolor ut justo. Morbi tempor eros nec nisi dignissim scelerisque. Phasellus quis est dui.
        COMMENT);

        $this->assertTrue($this->commentModel->checkIfNewExperience($original, $updated));
    }

    public function testLongTextWithLowNumberUpdatesIsNotANewExperience()
    {
        $original = new Comment();
        $original->setTextFree(<<<COMMENT
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sodales scelerisque aliquam. Curabitur viverra quam ornare ligula tempus vehicula. Pellentesque vehicula eros at urna faucibus luctus. Donec et posuere ipsum. Nulla massa nunc, pulvinar sit amet sodales at, euismod volutpat sem. Nulla pharetra velit nibh, sollicitudin vulputate enim vestibulum vitae. Vestibulum lacinia, urna varius aliquet aliquet, nisi metus posuere urna, iaculis interdum tellus felis nec neque. Morbi sed semper orci, eu accumsan libero. Donec ullamcorper libero vel eleifend blandit. Proin id mauris libero. Morbi egestas convallis condimentum. Aliquam vitae lectus sapien. Donec id accumsan arcu. Nulla porta, nisl ac egestas hendrerit, purus metus tincidunt ex, nec maximus sapien ex condimentum turpis.

        Sed faucibus magna et tellus tempor pulvinar. Sed tristique urna quam, sed tincidunt risus scelerisque vel. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Vestibulum elementum hendrerit tortor non tempus. Curabitur cursus lacus maximus, varius nibh ut, mollis ex. Nullam eget neque at arcu laoreet vestibulum sed in velit. Etiam nec nunc nec ligula suscipit ornare.

        In fringilla dapibus mattis. Sed sed sapien lacus. Suspendisse nec libero vitae quam ullamcorper interdum at a magna. Curabitur blandit ipsum at dui ultricies vulputate. Morbi accumsan justo nulla, eu semper mauris volutpat in. Maecenas vestibulum lacus sit amet elit tristique, quis rutrum arcu pellentesque. Maecenas tincidunt convallis diam.

        Proin laoreet ligula a justo rutrum, eu rhoncus dui euismod. Nunc consectetur dolor sem, tincidunt finibus nisl rhoncus tristique. Mauris tincidunt libero sit amet mauris congue, ut elementum libero tristique. Nullam ut sem cursus, condimentum libero condimentum, accumsan dolor. Duis bibendum nulla ut pharetra eleifend. Etiam orci leo, rhoncus non ultrices sit amet, fermentum non arcu. Aenean bibendum dui massa, quis vulputate sapien pretium nec. Pellentesque quis justo laoreet, varius massa ut, sagittis nisl. Sed id ligula ac mauris aliquam tempus. Etiam libero nulla, volutpat efficitur interdum sed, accumsan sodales ex. Nam pretium sem vel auctor vulputate. Praesent in nibh lacinia, malesuada dolor sit amet, commodo turpis. Aenean ultrices ex facilisis, facilisis augue vitae, suscipit ex. Fusce viverra ex metus. Nam feugiat mi quis ipsum aliquet pretium.

        Nam vestibulum rutrum convallis. Mauris et finibus nunc. Cras dictum aliquet varius. Nam hendrerit convallis lorem, ut gravida erat auctor ac. Duis ultricies quis sem nec laoreet. Sed suscipit est nec odio porta consequat. Donec mauris purus, pretium eget massa a, accumsan fermentum tortor.

        Sed et convallis dolor. Praesent pellentesque egestas turpis sit amet interdum. Sed ac lectus auctor, sollicitudin elit a, lacinia augue. Aenean et vehicula ex. Nulla hendrerit quis urna eu vestibulum. Integer vitae velit luctus, hendrerit magna vitae, pretium ligula. Morbi vel pulvinar lorem, ac rhoncus sem. In non gravida dui, id commodo ante. Morbi pharetra, orci id tristique dapibus, lectus lorem maximus neque, vitae gravida felis libero vitae felis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Ut ac cursus ligula. Proin tempus sem lorem, at vestibulum ante eleifend vitae.

        Aliquam non elit egestas, ultricies felis euismod, pulvinar nibh. Ut porta arcu vitae est cursus consectetur. Nulla consequat quam sed lorem tempor ultrices. Pellentesque fringilla, magna et euismod lacinia, erat leo fermentum dui, sed sagittis augue neque quis lectus. Sed dignissim laoreet lorem ac porttitor. Etiam non euismod tellus, nec finibus purus. Aliquam vitae porttitor ligula, quis aliquet risus. Sed tempor fermentum dapibus. Fusce quis tellus tincidunt, sollicitudin leo non, ultrices sapien. Nullam id lectus turpis. Integer tempor cursus aliquam. Fusce sed dapibus massa, eget condimentum enim. Duis eu pretium nulla, et aliquam nisl. Curabitur ultricies, arcu sit amet venenatis finibus, tortor quam luctus ex, sit amet hendrerit ante lacus sit amet sem.

        Donec fringilla vehicula ex quis tincidunt. In eu posuere justo. Nulla ut odio lacus. Nam aliquam sollicitudin feugiat. Pellentesque posuere quis quam in malesuada. Vestibulum consequat ante et vestibulum viverra. Nam auctor tristique mi at aliquam. Suspendisse potenti. Curabitur efficitur augue vitae pellentesque ultrices. Sed efficitur lobortis tempor. Ut sollicitudin dolor urna, sit amet imperdiet nisi viverra eget. Nullam nec venenatis ipsum, vel dapibus sapien. Ut a leo consectetur, scelerisque nulla sagittis, finibus sem.

        Duis condimentum egestas nibh, at viverra diam sagittis sed. Curabitur sit amet nisi elementum, consequat justo eget, pulvinar leo. Vivamus suscipit interdum mi et tempor. Aenean at ante nec ante venenatis aliquam eu at diam. Fusce at augue maximus, auctor eros nec, lobortis lorem. Integer venenatis justo varius enim porta volutpat a a augue. Integer eu risus ut turpis vulputate scelerisque at non leo. Phasellus fringilla cursus condimentum. Phasellus at bibendum erat. Phasellus at arcu id dolor volutpat auctor. Quisque ultricies nulla ac erat pulvinar faucibus.

        Donec elementum bibendum tellus eget tincidunt. Aenean congue tempus ultricies. Suspendisse commodo leo risus. Donec enim nunc, tempus et convallis ac, malesuada nec magna. Quisque tellus nibh, sollicitudin eu justo ut, dignissim bibendum lorem. Cras ac ex et purus commodo faucibus. In hac habitasse platea dictumst. Maecenas sodales porta arcu vel faucibus. Aliquam dignissim sodales semper. Sed sed ultrices purus, nec faucibus elit. Phasellus eget purus dolor.
        COMMENT);

        $updated = new Comment();
        $updated->setTextFree(<<<COMMENT
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sodales scelerisque aliquam. Curabitur viverra quam ornare ligula tempus vehicula. Pellentesque vehicula eros at urna faucibus luctus. Donec et posuere ipsum. Nulla massa nunc, pulvinar sit amet sodales at, euismod volutpat sem. Nulla pharetra velit nibh, sollicitudin vulputate enim vestibulum vitae. Vestibulum lacinia, urna varius aliquet aliquet, nisi metus posuere urna, iaculis interdum tellus felis nec neque. Morbi sed semper orci, eu accumsan libero. Donec ullamcorper libero vel eleifend blandit. Proin id mauris libero. Morbi egestas convallis condimentum. Aliquam vitae lectus sapien. Donec id accumsan arcu. Nulla porta, nisl ac egestas hendrerit, purus metus tincidunt ex, nec maximus sapien ex condimentum turpis.

        Sed faucibus magna et tellus tempor pulvinar. Sed tristique urna quam, sed tincidunt risus scelerisque vel. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Vestibulum elementum hendrerit tortor non tempus. Curabitur cursus lacus maximus, varius nibh ut, mollis ex. Nullam eget neque at arcu laoreet vestibulum sed in velit. Etiam nec nunc nec ligula suscipit ornare.

        In fringilla dapibus mattis. Sed sed sapien lacus. Suspendisse nec libero vitae quam ullamcorper interdum at a magna. Curabitur blandit ipsum at dui ultricies vulputate. Morbi accumsan justo nulla, eu semper mauris volutpat in. Maecenas vestibulum lacus sit amet elit tristique, quis rutrum arcu pellentesque. Maecenas tincidunt convallis diam.

        Proin laoreet ligula a justo rutrum, eu rhoncus dui euismod. Nunc consectetur dolor sem, tincidunt finibus nisl rhoncus tristique. Mauris tincidunt libero sit amet mauris congue, ut elementum libero tristique. Nullam ut sem cursus, condimentum libero condimentum, accumsan dolor. Duis bibendum nulla ut pharetra eleifend. Etiam orci leo, rhoncus non ultrices sit amet, fermentum non arcu. Aenean bibendum dui massa, quis vulputate sapien pretium nec. Pellentesque quis justo laoreet, varius massa ut, sagittis nisl. Sed id ligula ac mauris aliquam tempus. Etiam libero nulla, volutpat efficitur interdum sed, accumsan sodales ex. Nam pretium sem vel auctor vulputate. Praesent in nibh lacinia, malesuada dolor sit amet, commodo turpis. Aenean ultrices ex facilisis, facilisis augue vitae, suscipit ex. Fusce viverra ex metus. Nam feugiat mi quis ipsum aliquet pretium.

        Nam vestibulum rutrum convallis. Mauris et finibus nunc. Cras dictum aliquet varius. Nam hendrerit convallis lorem, ut gravida erat auctor ac. Duis ultricies quis sem nec laoreet. Sed suscipit est nec odio porta consequat. Donec mauris purus, pretium eget massa a, accumsan fermentum tortor.

        Sed et convallis dolor. Praesent pellentesque egestas turpis sit amet interdum. Sed ac lectus auctor, sollicitudin elit a, lacinia augue. Aenean et vehicula ex. Nulla hendrerit quis urna eu vestibulum. Integer vitae velit luctus, hendrerit magna vitae, pretium ligula. Morbi vel pulvinar lorem, ac rhoncus sem. In non gravida dui, id commodo ante. Morbi pharetra, orci id tristique dapibus, lectus lorem maximus neque, vitae gravida felis libero vitae felis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Ut ac cursus ligula. Proin tempus sem lorem, at vestibulum ante eleifend vitae.

        Aliquam non elit egestas, ultricies felis euismod, pulvinar nibh. Ut porta arcu vitae est cursus consectetur. Nulla consequat quam sed lorem tempor ultrices. Pellentesque fringilla, magna et euismod lacinia, erat leo fermentum dui, sed sagittis augue neque quis lectus. Sed dignissim laoreet lorem ac porttitor. Etiam non euismod tellus, nec finibus purus. Aliquam vitae porttitor ligula, quis aliquet risus. Sed tempor fermentum dapibus. Fusce quis tellus tincidunt, sollicitudin leo non, ultrices sapien. Nullam id lectus turpis. Integer tempor cursus aliquam. Fusce sed dapibus massa, eget condimentum enim. Duis eu pretium nulla, et aliquam nisl. Curabitur ultricies, arcu sit amet venenatis finibus, tortor quam luctus ex, sit amet hendrerit ante lacus sit amet sem.

        Donec fringilla vehicula ex quis tincidunt. In eu posuere justo. Nulla ut odio lacus. Nam aliquam sollicitudin feugiat. Pellentesque posuere quis quam in malesuada. Vestibulum consequat ante et vestibulum viverra. Nam auctor tristique mi at aliquam. Suspendisse potenti. Curabitur efficitur augue vitae pellentesque ultrices. Sed efficitur lobortis tempor. Ut sollicitudin dolor urna, sit amet imperdiet nisi viverra eget. Nullam nec venenatis ipsum, vel dapibus sapien. Ut a leo consectetur, scelerisque nulla sagittis, finibus sem.

        Duis condimentum egestas nibh, at viverra diam sagittis sed. Curabitur sit amet nisi elementum, consequat justo eget, pulvinar leo. Vivamus suscipit interdum mi et tempor. Aenean at ante nec ante venenatis aliquam eu at diam. Fusce at augue maximus, auctor eros nec, lobortis lorem. Integer venenatis justo varius enim porta volutpat a a augue. Integer eu risus ut turpis vulputate scelerisque at non leo. Phasellus fringilla cursus condimentum. Phasellus at bibendum erat. Phasellus at arcu id dolor volutpat auctor. Quisque ultricies nulla ac erat pulvinar faucibus.

        *Donec* elementum bibendum tellus eget tincidunt. Aenean congue tempus ultricies. Suspendisse commodo leo risus. Donec enim nunc, tempus et convallis ac, malesuada nec magna. Quisque tellus nibh, sollicitudin eu justo ut, dignissim bibendum lorem. Cras ac ex et purus commodo faucibus. In hac habitasse platea dictumst. Maecenas sodales porta arcu vel faucibus. Aliquam dignissim sodales semper. Sed sed ultrices purus, nec faucibus elit. Phasellus eget purus dolor.
        COMMENT);

        $this->assertFalse($this->commentModel->checkIfNewExperience($original, $updated));
    }
    private function buildRelations(array $relations): string
    {
        return implode(',', $relations);
    }

    private function addRelations(string $relations, array $additionalRelations): string
    {
        // turn relations into array.
        $relations = explode(',', $relations);

        return implode(',', array_merge($relations, $additionalRelations));
    }
}
