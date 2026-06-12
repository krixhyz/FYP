
const {Document,Packer,Paragraph,TextRun,Table,TableRow,TableCell,BorderStyle,WidthType,ShadingType,convertInchesToTwip}=require('docx');
const fs=require('fs'),path=require('path');
const mdPath=path.join(__dirname,'iteration_4_swap_system.md');
const outPath=path.join(__dirname,'iteration_4_swap_system.docx');
const md=fs.readFileSync(mdPath,'utf8');
const DARK='1A1C1C',GREEN='006A38',GREY='555555',WHITE='FFFFFF',BD='CCCCCC',FIGBG='EEF7F2',FONT='Calibri';
const r=(t,o={})=>new TextRun({text:t,font:o.font||FONT,size:o.size??22,bold:o.bold??false,italics:o.italics??false,color:o.color||DARK});
function inline(text,base={}){const parts=[],re=/(\*\*(.+?)\*\*|\*(.+?)\*|`(.+?)`)/g;let last=0,m;while((m=re.exec(text))!==null){if(m.index>last)parts.push(r(text.slice(last,m.index),base));if(m[2])parts.push(r(m[2],{...base,bold:true}));else if(m[3])parts.push(r(m[3],{...base,italics:true}));else if(m[4])parts.push(r(m[4],{...base,font:'Courier New',size:20,color:GREEN}));last=m.index+m[0].length;}if(last<text.length)parts.push(r(text.slice(last),base));return parts.length?parts:[r(text,base)];}
function h(text,level){const map={1:{sz:38,color:GREEN,bold:true,bef:0,aft:300,brdr:false},2:{sz:28,color:GREEN,bold:true,bef:400,aft:160,brdr:true},3:{sz:24,color:DARK,bold:true,bef:280,aft:120,brdr:false},4:{sz:22,color:GREY,bold:true,bef:200,aft:80,brdr:false}};const c=map[level]||map[4];return new Paragraph({children:[new TextRun({text,font:FONT,size:c.sz,bold:c.bold,color:c.color})],spacing:{before:c.bef,after:c.aft},border:c.brdr?{bottom:{style:BorderStyle.SINGLE,size:6,color:GREEN,space:4}}:undefined});}
function figLine(text){return new Paragraph({children:[r(text,{italics:true,color:GREY,size:20})],spacing:{before:120,after:120},indent:{left:convertInchesToTwip(0.3)},border:{left:{style:BorderStyle.SINGLE,size:12,color:GREEN,space:6}},shading:{type:ShadingType.CLEAR,fill:FIGBG}});}
function caption(text){return new Paragraph({children:[r(text,{italics:true,color:GREY,size:19})],spacing:{before:60,after:180},alignment:'center'});}
function buildTable(rows){return new Table({width:{size:100,type:WidthType.PERCENTAGE},borders:{top:{style:BorderStyle.SINGLE,size:4,color:BD},bottom:{style:BorderStyle.SINGLE,size:4,color:BD},left:{style:BorderStyle.SINGLE,size:4,color:BD},right:{style:BorderStyle.SINGLE,size:4,color:BD},insideH:{style:BorderStyle.SINGLE,size:4,color:BD},insideV:{style:BorderStyle.SINGLE,size:4,color:BD}},rows:rows.map((cells,ri)=>new TableRow({tableHeader:ri===0,children:cells.map(ct=>new TableCell({children:[new Paragraph({children:inline(ct,{bold:ri===0,color:ri===0?WHITE:DARK,size:20}),spacing:{before:60,after:60}})],shading:{type:ShadingType.CLEAR,fill:ri===0?GREEN:WHITE},margins:{top:80,bottom:80,left:120,right:120}}))}))});}
function parse(md){const els=[],lines=md.split('\n');let i=0;while(i<lines.length){const raw=lines[i],t=raw.trim();if(!t){i++;continue;}
const hm=t.match(/^(#{1,4})\s+(.*)/);if(hm){els.push(h(hm[2],hm[1].length));i++;continue;}
if(t.startsWith('|')){const tbl=[];while(i<lines.length&&lines[i].trim().startsWith('|')){const tl=lines[i].trim();if(!/^\|[\s\-|:]+\|$/.test(tl))tbl.push(tl.split('|').map(c=>c.trim()).filter(Boolean));i++;}if(tbl.length>1){els.push(buildTable(tbl));}continue;}
if(/^\[Figure/.test(t)){els.push(figLine(t));i++;continue;}
if(/^Table\s+\d/.test(t)){els.push(caption(t));i++;continue;}
if(/^[-•]\s/.test(t)){els.push(new Paragraph({children:inline(t.replace(/^[-•]\s+/,'')),bullet:{level:0},spacing:{before:60,after:60}}));i++;continue;}
els.push(new Paragraph({children:inline(t),spacing:{before:80,after:120}}));i++;}return els;}
const doc=new Document({creator:'ReLoop FYP',title:'Iteration 4 — Swap Transaction System',styles:{default:{document:{run:{font:FONT,size:22,color:DARK}}}},sections:[{properties:{page:{margin:{top:convertInchesToTwip(1),bottom:convertInchesToTwip(1),left:convertInchesToTwip(1.25),right:convertInchesToTwip(1.25)}}},children:parse(md)}]});
Packer.toBuffer(doc).then(buf=>{fs.writeFileSync(outPath,buf);console.log('Done:',Math.round(buf.length/1024)+'KB');});
