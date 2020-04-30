{extend name="public/container"}
{block name="head_top"}

{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <!--搜索条件-->
        <!--end-->

    </div>
    <!--列表-->
    <div class="layui-row layui-col-space15" >
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">认证列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container" id="container-action">
                        <button class="layui-btn layui-btn-sm demoReload" data-type="1" >全部</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="2">未审核</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="3">已通过</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="4">未通过</button>
<!--                        <button class="layui-btn layui-btn-sm" data-type="del_order">批量删除订单</button>-->
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--用户信息-->
                    <script type="text/html" id="user_id">
                        {{d.user_id}}
                    </script>
                    <script type="text/html" id="card_z">
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.card_z}}">
                    </script>
                    <script type="text/html" id="card_f">
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.card_f}}">
                    </script>
                    <script type="text/html" id="certificate">
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.certificate}}">
                    </script>
                    <script type="text/html" id="career_type">
                        {{#  if(d.career_type==1){ }}
                        水电工
                        {{#  }else if(d.career_type==2){ }}
                        家电维修
                        {{#  }else if(d.career_type==3){ }}
                        开锁匠
                        {{#  }else if(d.career_type==4){ }}
                        瓦工
                        {{#  }else if(d.career_type==5){ }}
                        油漆工
                        {{#  }else { }}
                        {{d.career_type}}
                        {{#  }; }}
                    </script>
                    <script type="text/html" id="status">
                        {{#  if(d.status==1){ }}
                        待审核
                        {{#  }else if(d.status==2){ }}
                        已通过
                        {{#  }else if(d.status==3){ }}
                        未通过
                        {{#  }else { }}
                        未知
                        {{#  }; }}
                    </script>
                    <script type="text/html" id="examine">
                        {{#  if(d.status==1){ }}
                        <a href="javascript:void(0);" lay-event='examine' class="layui-btn layui-btn-xs">
                            <i class="fa fa-file-text"></i> 审核
                        </a>
                        {{#  }else if(d.status==2){ }}
                        已通过
                        {{#  }else if(d.status==3){ }}
                        未通过
                        {{#  }else { }}
                        未知
                        {{#  }; }}
                    </script>
                    <script type="text/html" id="buttonTpl">

                    </script>
                </div>
            </div>
        </div>
    </div>
    <!--end-->
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    layui.use(['layer','table'], function() { //独立版的layer无需执行这一句
        var table = layui.table;
        $('.demoReload').click(function () {
            var val=$(this).attr("data-type");
            table.reload('LISTS', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    key: val
                }
            }, 'data');
        })
    })
    layList.tableList('List',"{:Url('worker_list')}",function (){
        return [
            {field:'user_id',templet:'#user_id',sort: true,event:'user_id',type:'checkbox'},
            {field: 'name', title: '工人姓名',width:'6%',align:'center'},
            {field: 'id_card', title: '身份证号',width:'10%',align:'center'},
            {field: 'address', title: '地址',width:'10%',align:'center'},
            {field: 'card_z', title: '身份证正面',templet:'#card_z',width:'12%',align:'center'},
            {field: 'card_f', title: '身份证反面',templet:'#card_f',width:'12%',align:'center'},
            // {field: 'paid', title: '支付状态',templet:'#paid',width:'8%',align:'center'},
            {field: 'certificate', title: '证书',templet:'#certificate',width:'11%',align:'center'},
            {field: 'career_type', title: '工人类别',templet:'#career_type',width:'8%',align:'center'},
            {field: 'status', title: '认证状态',templet:'#status',width:'6%',align:'center'},
            {field: 'examine', title: '操作',align:'center',templet: '#examine',width:'10%'},
        ];
    },10,20,'LISTS');

    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'order_paid':
            case 'examine':
                $eb.createModalFrame(data.name+'认证信息详情',layList.U({a:'to_examine',q:{uid:data.user_id}}));
                break;
        }
    })

    $('#container-action').find('button').each(function () {
        $(this).on('click',function(){
            var act = $(this).data('type');
            action[act] && action[act]();
        });
    })
    //下拉框
    $(document).click(function (e) {
        $('.layui-nav-child').hide();
    })
    function dropdown(that){
        var oEvent = arguments.callee.caller.arguments[0] || event;
        oEvent.stopPropagation();
        var offset = $(that).offset();
        var top=offset.top-$(window).scrollTop();
        var index = $(that).parents('tr').data('index');
        $('.layui-nav-child').each(function (key) {
            if (key != index) {
                $(this).hide();
            }
        })
        if($(document).height() < top+$(that).next('ul').height()){
            $(that).next('ul').css({
                'padding': 10,
                'top': - ($(that).parents('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parents('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
</script>
{/block}
