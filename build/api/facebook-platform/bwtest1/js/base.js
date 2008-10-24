var cur_picked = -1;
function over(id) {
  document.getElementById("sm_"+id).addClassName("box_over");
}
function out(id) {
  document.getElementById("sm_"+id).removeClassName("box_over");
}
function select(title, mood, id) {
  document.getElementById("sm_"+id).addClassName("box_selected");
  document.getElementById("picked").setValue(id);
}

function final(template_id, image_src, base, callback, title, emote, id) {
  select(title, emote, id);
  var image =  image_src + id + ".jpg";
  var template_data = {'mood': title,
                       'emote': emote,
                       'mood_src': image,
                       'images' : [{'href':'http://www.facebook.com' , 'src' : image}]};


  var ajax = new Ajax();
  ajax.responseType = Ajax.RAW;
  ajax.post(callback+'handlers/jsFeed.php', {'picked':id});

  Facebook.showFeedDialog(template_id, template_data, '', [],
                          function() {document.setLocation(base + 'mysmilies.php');});
}

function unselect(id) {
  document.getElementById("sm_"+id).removeClassName("box_selected");
}

function picked(i) {
  if (cur_picked!=-1) {
    unselect(cur_picked);
  }
  cur_picked = i;
  select(i);
}
