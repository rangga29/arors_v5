document.addEventListener("alpine:init",()=>{Alpine.data("form",()=>({birthday:"",initCleave(){const t=this.$refs.birthdayInput;new Cleave(t,{date:!0,datePattern:["d","m","Y"]})}}))});
