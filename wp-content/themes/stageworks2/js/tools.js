(function(){
var s = "", p = 0, random;

random = function(type){
  var n = Math.random();
  n = n - 0.5; //now is zero centred
  if (type === 'degree') {
    n = n/3;
    return String( (Math.floor(n*100)/100) ).substring(0, 5) + 'deg';
  }
  if (type === 'scale') {
    n = n/50;
    n = n + 1; //now is one centred
    return String( (Math.floor(n*100)/100) ).substring(0, 5);
  }
  if (type === 'opacity') {
    n = n - 0.5; //now is -0.5 centred
    n = n/3;
    n = n + 1;
    return String(n).substring(0, 5);
  }
  if (type === 'translate') {
    n = n * 2;
    return String( Math.round(n) ) + 'px';
  }
};

for (; p<100; p += 2) {
  s += p + '% {\n';
  s += '  opacity: ' + random('opacity') + ';\n';
  s += '  @include transform(' +
    'translate(' + random('translate') + ', ' + random('translate') + ')' +
    ');\n';
  s += '}\n';
}
console.log(s);
}());


