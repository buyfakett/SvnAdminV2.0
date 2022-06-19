webpackJsonp([7],{376:function(t,s,e){e(399);var a=e(147)(e(386),e(416),null,null);a.options.__file="D:\\SVN\\B06.svn管理面板V2.0\\09.软件开发\\01.web\\src\\views\\index\\index.vue",a.esModule&&Object.keys(a.esModule).some(function(t){return"default"!==t&&"__esModule"!==t})&&console.error("named exports are not supported in *.vue files."),a.options.functional&&console.error("[vue-loader] index.vue: functional components are not supported with templates, they should use render functions."),t.exports=a.exports},386:function(t,s,e){"use strict";Object.defineProperty(s,"__esModule",{value:!0}),s.default={data:function(){return{display:{part1:!0,part2:!0},diskList:[],statusInfo:{load:{cpuLoad15Min:.22,cpuLoad5Min:.28,cpuLoad1Min:.32,percent:16,color:"#28bcfe"},cpu:{percent:28.2,cpu:["Intel(R) Xeon(R) Platinum 8255C CPU @ 2.50GHz"],cpuPhysical:1,cpuPhysicalCore:1,cpuCore:1,cpuProcessor:1,color:"#28bcfe"},mem:{memTotal:1838,memUsed:975,memFree:863,percent:53,color:"#28bcfe"}},systemBrif:{os:"",repSize:0,repCount:0,repUser:0,repGroup:0,logCount:0,backupSize:0}}},computed:{},created:function(){},mounted:function(){var t=this;t.display.part1&&(t.GetDisk(),t.GetSystemStatus(),t.timer=window.setInterval(function(){setTimeout(t.GetSystemStatus(),0)},3e3),t.$once("hook:beforeDestroy",function(){clearInterval(t.timer)})),t.display.part2&&t.GetSystemAnalysis()},methods:{GetDisk:function(){var t=this,s={};t.$axios.post("/api.php?c=Statistics&a=GetDisk&t=web",s).then(function(s){var e=s.data;1==e.status?t.diskList=e.data:t.$Message.error(e.message)}).catch(function(s){console.log(s),t.$Message.error("出错了 请联系管理员！")})},GetSystemStatus:function(){var t=this,s={};t.$axios.post("/api.php?c=Statistics&a=GetSystemStatus&t=web",s).then(function(s){var e=s.data;1==e.status?t.statusInfo=e.data:t.$Message.error(e.message)}).catch(function(s){console.log(s),t.$Message.error("出错了 请联系管理员！")})},GetSystemAnalysis:function(){var t=this,s={};t.$axios.post("/api.php?c=Statistics&a=GetSystemAnalysis&t=web",s).then(function(s){var e=s.data;1==e.status?t.systemBrif=e.data:t.$Message.error(e.message)}).catch(function(s){console.log(s),t.$Message.error("出错了 请联系管理员！")})}}}},399:function(t,s){},416:function(t,s,e){t.exports={render:function(){var t=this,s=t.$createElement,e=t._self._c||s;return e("div",[t.display.part1?e("Card",{staticStyle:{"margin-bottom":"10px"},attrs:{bordered:!1,"dis-hover":!0}},[e("p",{attrs:{slot:"title"},slot:"title"},[e("Icon",{attrs:{type:"md-bulb"}}),t._v("\n      "+t._s(t.systemBrif.os)+"\n    ")],1),t._v(" "),e("div",[e("Row",[e("Col",{attrs:{span:"4"}},[e("div",{staticClass:"statusTop"},[t._v("负载状态")]),t._v(" "),e("Tooltip",{attrs:{placement:"bottom","max-width":"200"}},[e("i-circle",{staticClass:"statusCircle",attrs:{percent:t.statusInfo.load.percent,dashboard:"",size:100,"stroke-color":t.statusInfo.load.color}},[e("span",{staticClass:"demo-circle-inner",staticStyle:{"font-size":"24px"}},[t._v(t._s(t.statusInfo.load.percent)+"%")])]),t._v(" "),e("div",{attrs:{slot:"content"},slot:"content"},[e("p",[t._v("最近1分钟平均负载："+t._s(t.statusInfo.load.cpuLoad1Min))]),t._v(" "),e("p",[t._v("最近5分钟平均负载："+t._s(t.statusInfo.load.cpuLoad5Min))]),t._v(" "),e("p",[t._v("最近15分钟平均负载："+t._s(t.statusInfo.load.cpuLoad15Min))])])],1),t._v(" "),e("div",{staticClass:"statusBottom"},[t._v(t._s(t.statusInfo.load.title))])],1),t._v(" "),e("Col",{attrs:{span:"4"}},[e("div",{staticClass:"statusTop"},[t._v("CPU使用率")]),t._v(" "),e("Tooltip",{attrs:{placement:"bottom","max-width":"200"}},[e("i-circle",{staticClass:"statusCircle",attrs:{percent:t.statusInfo.cpu.percent,dashboard:"",size:100,"stroke-color":t.statusInfo.cpu.color}},[e("span",{staticClass:"demo-circle-inner",staticStyle:{"font-size":"24px"}},[t._v(t._s(t.statusInfo.cpu.percent)+"%")])]),t._v(" "),e("div",{attrs:{slot:"content"},slot:"content"},[t._l(t.statusInfo.cpu.cpu,function(s){return e("p",{key:s},[t._v(t._s(s))])}),t._v(" "),e("p",[t._v("物理CPU个数："+t._s(t.statusInfo.cpu.cpuPhysical))]),t._v(" "),e("p",[t._v("物理CPU的总核心数："+t._s(t.statusInfo.cpu.cpuCore))]),t._v(" "),e("p",[t._v("物理CPU的线程总数："+t._s(t.statusInfo.cpu.cpuProcessor))])],2)],1),t._v(" "),e("div",{staticClass:"statusBottom"},[t._v(t._s(t.statusInfo.cpu.cpuCore)+"核心")])],1),t._v(" "),e("Col",{attrs:{span:"4"}},[e("div",{staticClass:"statusTop"},[t._v("内存使用率")]),t._v(" "),e("i-circle",{staticClass:"statusCircle",attrs:{percent:t.statusInfo.mem.percent,dashboard:"",size:100,"stroke-color":t.statusInfo.mem.color}},[e("span",{staticClass:"demo-circle-inner",staticStyle:{"font-size":"24px"}},[t._v(t._s(t.statusInfo.mem.percent)+"%")])]),t._v(" "),e("div",{staticClass:"statusBottom"},[t._v("\n            "+t._s(t.statusInfo.mem.memUsed)+" / "+t._s(t.statusInfo.mem.memTotal)+"(MB)\n          ")])],1),t._v(" "),t._l(t.diskList,function(s,a){return e("Col",{key:a,attrs:{span:"4"}},[e("div",{staticClass:"statusTop"},[t._v(t._s(s.mountedOn))]),t._v(" "),e("Tooltip",{attrs:{placement:"bottom","max-width":"200"}},[e("div",{attrs:{slot:"content"},slot:"content"},[e("p",[t._v("文件系统："+t._s(s.fileSystem))]),t._v(" "),e("p",[t._v("容量："+t._s(s.size))]),t._v(" "),e("p",[t._v("已使用："+t._s(s.used))]),t._v(" "),e("p",[t._v("可使用："+t._s(s.avail))]),t._v(" "),e("p",[t._v("使用率："+t._s(s.percent)+"%")]),t._v(" "),e("p",[t._v("挂载点："+t._s(s.mountedOn))])]),t._v(" "),e("i-circle",{staticClass:"statusCircle",attrs:{percent:s.percent,dashboard:"",size:100,"stroke-color":s.color}},[e("span",{staticClass:"demo-circle-inner",staticStyle:{"font-size":"24px"}},[t._v(t._s(s.percent)+"%")])])],1),t._v(" "),e("div",{staticClass:"statusBottom"},[t._v("\n            "+t._s(s.used)+" /\n            "+t._s(s.size)+"\n          ")])],1)})],2)],1)]):t._e(),t._v(" "),e("Card",{staticStyle:{"margin-bottom":"10px"},attrs:{bordered:!1,"dis-hover":!0}},[e("p",{attrs:{slot:"title"},slot:"title"},[e("Icon",{attrs:{type:"ios-options"}}),t._v("\n      统计\n    ")],1),t._v(" "),e("div",[e("Row",{attrs:{gutter:16}},[e("Col",{attrs:{span:"4"}},[e("Card",{attrs:{"dis-hover":!0}},[e("div",{staticStyle:{"text-align":"center"}},[e("p",[t._v("仓库占用")]),t._v(" "),e("h2",{staticStyle:{color:"#28bcfe"}},[t._v(t._s(t.systemBrif.repSize))])])])],1),t._v(" "),e("Col",{attrs:{span:"4"}},[e("Card",{attrs:{"dis-hover":!0}},[e("div",{staticStyle:{"text-align":"center"}},[e("p",[t._v("备份占用")]),t._v(" "),e("h2",{staticStyle:{color:"#28bcfe"}},[t._v(t._s(t.systemBrif.backupSize))])])])],1),t._v(" "),e("Col",{attrs:{span:"4"}},[e("Card",{attrs:{"dis-hover":!0}},[e("div",{staticStyle:{"text-align":"center"}},[e("p",[t._v("SVN仓库")]),t._v(" "),e("h2",{staticStyle:{color:"#28bcfe"}},[t._v(t._s(t.systemBrif.repCount))])])])],1),t._v(" "),e("Col",{attrs:{span:"4"}},[e("Card",{attrs:{"dis-hover":!0}},[e("div",{staticStyle:{"text-align":"center"}},[e("p",[t._v("SVN用户")]),t._v(" "),e("h2",{staticStyle:{color:"#28bcfe"}},[t._v(t._s(t.systemBrif.repUser))])])])],1),t._v(" "),e("Col",{attrs:{span:"4"}},[e("Card",{attrs:{"dis-hover":!0}},[e("div",{staticStyle:{"text-align":"center"}},[e("p",[t._v("SVN分组")]),t._v(" "),e("h2",{staticStyle:{color:"#28bcfe"}},[t._v(t._s(t.systemBrif.repGroup))])])])],1),t._v(" "),e("Col",{attrs:{span:"4"}},[e("Card",{attrs:{"dis-hover":!0}},[e("div",{staticStyle:{"text-align":"center"}},[e("p",[t._v("运行日志/条")]),t._v(" "),e("h2",{staticStyle:{color:"#28bcfe"}},[t._v(t._s(t.systemBrif.logCount))])])])],1)],1)],1)])],1)},staticRenderFns:[]},t.exports.render._withStripped=!0}});