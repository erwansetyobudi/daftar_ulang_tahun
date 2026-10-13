(function () {
  'use strict';
  var params = new URLSearchParams(window.location.search);
  var name = params.get('name');
  if (!name) {
    var match = window.location.pathname.match(/\/happybirthdayto=([^/]+)\/?$/i);
    if (match) { try { name = decodeURIComponent(match[1]); } catch (e) { name = match[1]; } }
  }
  name = (name || 'Yustina').replace(/\+/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 60) || 'Yustina';
  var letters = Array.from(name.toLocaleUpperCase()).filter(function (c) { return c !== ' '; });
  var colors = ['#F2B300','#0719D4','#D14D39','#8FAD00','#8377E4','#99C96A','#20CFB4'];
  var container = document.getElementById('birthday-balloons');
  letters.forEach(function (letter, index) {
    var balloon = document.createElement('div');
    balloon.className = 'balloons text-center';
    balloon.id = 'b' + (index + 1);
    balloon.style.setProperty('--balloon-color', colors[index % colors.length]);
    var heading = document.createElement('h2');
    heading.style.color = '#ffffff';
    heading.textContent = letter;
    balloon.appendChild(heading);
    var string = document.createElement("span");
    string.className = "balloon-string";
    balloon.appendChild(string);
    container.appendChild(balloon);
  });
  document.getElementById('birthday-name').textContent = name;
  document.title = 'Happy Birthday ' + name;
  window.birthdayName = name;
})();
