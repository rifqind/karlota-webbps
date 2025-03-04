const i=(e,o=400)=>{let t;return function(...u){clearTimeout(t),t=setTimeout(()=>{e.apply(this,u)},o)}};export{i as d};
