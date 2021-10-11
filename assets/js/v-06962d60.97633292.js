"use strict";(self.webpackChunk_myparcelnl_php_sdk_docs=self.webpackChunk_myparcelnl_php_sdk_docs||[]).push([[213],{674:(n,a,s)=>{s.r(a),s.d(a,{default:()=>P});var e=s(5793);const t=(0,e._)("h1",{id:"create-and-download-label-s",tabindex:"-1"},[(0,e._)("a",{class:"header-anchor",href:"#create-and-download-label-s","aria-hidden":"true"},"#"),(0,e.Uk)(" Create and download label(s)")],-1),o=(0,e.Uk)("Create and directly download PDF with "),l=(0,e._)("code",null,"setPdfOfLabels($position)",-1),p=(0,e.Uk)(" where "),c=(0,e._)("code",null,"$positions",-1),i=(0,e.Uk)(" is the "),d=(0,e.Uk)("label position"),r=(0,e.Uk)(" value."),u=(0,e.uE)('<div class="language-php ext-php line-numbers-mode"><pre class="language-php"><code><span class="token variable">$consignments</span>\n    <span class="token operator">-&gt;</span><span class="token function">setPdfOfLabels</span><span class="token punctuation">(</span><span class="token punctuation">)</span>\n    <span class="token comment">// Opens pdf &quot;inline&quot; by default, pass false as argument to download file instead</span>\n    <span class="token operator">-&gt;</span><span class="token function">downloadPdfOfLabels</span><span class="token punctuation">(</span><span class="token constant boolean">false</span><span class="token punctuation">)</span><span class="token punctuation">;</span> \n</code></pre><div class="line-numbers"><span class="line-number">1</span><br><span class="line-number">2</span><br><span class="line-number">3</span><br><span class="line-number">4</span><br></div></div>',1),k=(0,e.Uk)("Create and echo download link to PDF with "),f=(0,e._)("code",null,"setLinkOfLabels($position)",-1),b=(0,e.Uk)(" where "),m=(0,e._)("code",null,"$positions",-1),h=(0,e.Uk)(" is the "),g=(0,e.Uk)("label position"),w=(0,e.Uk)(" value. If you want more than 25 labels in one response, "),_=(0,e._)("code",null,"setLinkOfLabels",-1),y=(0,e.Uk)(" will automatically use a different endpoint. At that point, it is likely that the PDF is not ready yet. You should check periodically if the PDF is ready for download."),v=(0,e.uE)('<div class="language-php ext-php line-numbers-mode"><pre class="language-php"><code><span class="token keyword">echo</span> <span class="token variable">$consignments</span> \n    <span class="token operator">-&gt;</span><span class="token function">setLinkOfLabels</span><span class="token punctuation">(</span><span class="token variable">$positions</span><span class="token punctuation">)</span>\n    <span class="token operator">-&gt;</span><span class="token function">getLinkOfLabels</span><span class="token punctuation">(</span><span class="token punctuation">)</span><span class="token punctuation">;</span>\n</code></pre><div class="line-numbers"><span class="line-number">1</span><br><span class="line-number">2</span><br><span class="line-number">3</span><br></div></div><p>If you want to download a label at a later time, you can also use the following to fill the collection:</p><div class="language-php ext-php line-numbers-mode"><pre class="language-php"><code><span class="token variable">$consignments</span> <span class="token operator">=</span> <span class="token class-name static-context">MyParcelCollection</span><span class="token operator">::</span><span class="token function">findByReferenceIdentifier</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;order-146&#39;</span><span class="token punctuation">,</span> <span class="token string single-quoted-string">&#39;api_key_from_backoffice&#39;</span><span class="token punctuation">)</span><span class="token punctuation">;</span>\n<span class="token variable">$consignments</span>\n    <span class="token operator">-&gt;</span><span class="token function">setPdfOfLabels</span><span class="token punctuation">(</span><span class="token punctuation">)</span>\n    <span class="token operator">-&gt;</span><span class="token function">downloadPdfOfLabels</span><span class="token punctuation">(</span><span class="token punctuation">)</span><span class="token punctuation">;</span>\n</code></pre><div class="line-numbers"><span class="line-number">1</span><br><span class="line-number">2</span><br><span class="line-number">3</span><br><span class="line-number">4</span><br></div></div><p>Instead of <code>findByReferenceIdentifier()</code> you can also use <code>findManyByReferenceIdentifier()</code>, <code>find()</code> or <code>findMany()</code>.</p>',4),L=(0,e.Uk)("More information: "),U={},P=(0,s(3744).Z)(U,[["render",function(n,a){const s=(0,e.up)("RouterLink"),U=(0,e.up)("ApiLink");return(0,e.wg)(),(0,e.iD)(e.HY,null,[t,(0,e._)("p",null,[o,l,p,c,i,(0,e.Wm)(s,{to:"/02.examples/05.label-format-and-position.html"},{default:(0,e.w5)((()=>[d])),_:1}),r]),u,(0,e._)("p",null,[k,f,b,m,h,(0,e.Wm)(s,{to:"/02.examples/05.label-format-and-position.html"},{default:(0,e.w5)((()=>[g])),_:1}),w,_,y]),v,(0,e._)("p",null,[L,(0,e.Wm)(U,{to:"#6_F"})])],64)}]])},3744:(n,a)=>{a.Z=(n,a)=>{for(const[s,e]of a)n[s]=e;return n}},5526:(n,a,s)=>{s.r(a),s.d(a,{data:()=>e});const e={key:"v-06962d60",path:"/02.examples/10.create-and-download-labels.html",title:"Create and download label(s)",lang:"en-US",frontmatter:{title:"Create and download label(s)"},excerpt:"",headers:[],filePathRelative:"02.examples/10.create-and-download-labels.md",git:{updatedTime:1633685364e3}}}}]);