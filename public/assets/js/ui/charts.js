// Funcion del archivo: Dibuja graficos SVG reutilizables para dashboard y ventas.
/* Mini-canvas vanilla charts: línea, barras, dona. Sin dependencias. */
(function(){
  function setup(canvas){
    var r = canvas.getBoundingClientRect();
    var d = window.devicePixelRatio||1;
    canvas.width = r.width*d; canvas.height = r.height*d;
    var ctx = canvas.getContext('2d'); ctx.scale(d,d);
    return { ctx:ctx, w:r.width, h:r.height };
  }
  function roundRect(ctx,x,y,w,h,r){
    if (h<r*2) r=Math.max(0,h/2);
    ctx.beginPath();
    ctx.moveTo(x+r,y); ctx.lineTo(x+w-r,y); ctx.quadraticCurveTo(x+w,y,x+w,y+r);
    ctx.lineTo(x+w,y+h); ctx.lineTo(x,y+h); ctx.lineTo(x,y+r);
    ctx.quadraticCurveTo(x,y,x+r,y); ctx.closePath();
  }
  function line(canvas, data, opts){
    opts = opts||{};
    var s = setup(canvas), ctx=s.ctx, w=s.w, h=s.h;
    var pad=28, max=Math.max.apply(null,data)*1.15, min=0;
    var xs = data.map(function(_,i){ return pad + (i*(w-pad*2))/(data.length-1); });
    var ys = data.map(function(v){ return h-pad - ((v-min)/(max-min))*(h-pad*2); });
    ctx.strokeStyle = '#eef2f7'; ctx.lineWidth=1;
    for (var i=0;i<4;i++){ var y=pad+i*((h-pad*2)/3); ctx.beginPath(); ctx.moveTo(pad,y); ctx.lineTo(w-pad,y); ctx.stroke(); }
    var grad = ctx.createLinearGradient(0,pad,0,h-pad);
    grad.addColorStop(0,'rgba(58,81,209,.32)'); grad.addColorStop(1,'rgba(58,81,209,0)');
    ctx.beginPath(); ctx.moveTo(xs[0],h-pad);
    xs.forEach(function(x,i){ ctx.lineTo(x,ys[i]); });
    ctx.lineTo(xs[xs.length-1],h-pad); ctx.closePath(); ctx.fillStyle=grad; ctx.fill();
    ctx.beginPath(); ctx.strokeStyle = opts.color||'#1b2c8a'; ctx.lineWidth=2.5; ctx.lineJoin='round'; ctx.lineCap='round';
    xs.forEach(function(x,i){ i? ctx.lineTo(x,ys[i]) : ctx.moveTo(x,ys[i]); }); ctx.stroke();
    ctx.fillStyle = opts.color||'#1b2c8a';
    xs.forEach(function(x,i){ ctx.beginPath(); ctx.arc(x,ys[i],3.5,0,Math.PI*2); ctx.fill(); });
    if (opts.labels){
      ctx.fillStyle='#94a3b8'; ctx.font='11px "Work Sans",sans-serif'; ctx.textAlign='center';
      opts.labels.forEach(function(l,i){ ctx.fillText(l, xs[i], h-8); });
    }
  }
  function bars(canvas, data, opts){
    opts=opts||{};
    var s = setup(canvas), ctx=s.ctx, w=s.w, h=s.h;
    var pad=28, max=Math.max.apply(null,data)*1.15;
    var gap = (w-pad*2)/data.length;
    var bw = gap*.6;
    ctx.strokeStyle='#eef2f7';
    for (var i=0;i<4;i++){ var y=pad+i*((h-pad*2)/3); ctx.beginPath(); ctx.moveTo(pad,y); ctx.lineTo(w-pad,y); ctx.stroke(); }
    data.forEach(function(v,i){
      var bh = (v/max)*(h-pad*2);
      var x = pad + i*gap + (gap-bw)/2;
      var y = h-pad-bh;
      var g = ctx.createLinearGradient(0,y,0,h-pad);
      g.addColorStop(0, opts.color||'#3a51d1'); g.addColorStop(1, opts.color2||'#1b2c8a');
      ctx.fillStyle=g;
      roundRect(ctx,x,y,bw,bh,6); ctx.fill();
    });
    if (opts.labels){
      ctx.fillStyle='#94a3b8'; ctx.font='11px "Work Sans",sans-serif'; ctx.textAlign='center';
      opts.labels.forEach(function(l,i){ ctx.fillText(l, pad + i*gap + gap/2, h-8); });
    }
  }
  function donut(canvas, data, opts){
    opts=opts||{};
    var s = setup(canvas), ctx=s.ctx, w=s.w, h=s.h;
    var cx=w/2, cy=h/2, r=Math.min(w,h)/2-6, ir=r*.62;
    var total = data.reduce(function(a,b){return a+b.value},0);
    var start=-Math.PI/2;
    data.forEach(function(d){
      var a = (d.value/total)*Math.PI*2;
      ctx.beginPath(); ctx.moveTo(cx,cy);
      ctx.arc(cx,cy,r,start,start+a); ctx.closePath();
      ctx.fillStyle=d.color; ctx.fill();
      start+=a;
    });
    ctx.fillStyle='#fff'; ctx.beginPath(); ctx.arc(cx,cy,ir,0,Math.PI*2); ctx.fill();
    if (opts.center){
      ctx.fillStyle='#0f172a'; ctx.font='700 22px "Work Sans"'; ctx.textAlign='center'; ctx.textBaseline='middle';
      ctx.fillText(opts.center, cx, cy-4);
      if (opts.centerSub){ ctx.fillStyle='#64748b'; ctx.font='12px "Work Sans"'; ctx.fillText(opts.centerSub, cx, cy+16); }
    }
  }
  window.AJTCharts = { line:line, bars:bars, donut:donut };
})();