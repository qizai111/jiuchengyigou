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
                <div class="layui-card-header">直播间列表</div>
                <div class="layui-card-body">
                <!--    <div class="layui-btn-container" id="container-action">
                    <button class="layui-btn layui-btn-sm" data-type="del_order">123</button>
                    </div>-->
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--订单-->
                    <script type="text/html" id="order_id">
                        {{d.order_id}}<br/>
                    </script>
                    <!--用户信息-->
                    <script type="text/html" id="live_status">
                        {{#  if(d.live_status==101){ }}
                        直播中
                        {{#  }else if(d.live_status==102){ }}
                        未开始
                        {{#  }else if(d.live_status==103){ }}
                        已结束
                        {{#  }else if(d.live_status==104){ }}
                        禁播
                        {{#  }else if(d.live_status==105){ }}
                        暂停中
                        {{#  }else if(d.live_status==106){ }}
                        异常
                        {{#  }else if(d.live_status==107){ }}
                        已过期
                        {{#  }else { }}
                        {{d.live_status}}
                        {{#  }; }}
                    </script>

                    <script type="text/html" id="buttonTpl">
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="videotape"><i class="layui-icon layui-icon-edit"></i>录播回放</button>
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
    layList.tableList('List',"{:Url('roomInfo')}",function (){
        return [
            {type:'checkbox'},
            {field: 'roomid', title: '房间号',event:'roomid',width:'10%'},
            {field: 'name', title: '房间名',width:'15%',align:'center',event:'name'},
            {field: 'start_time', title: '开播时间',width:'15%',align:'center'},
            {field: 'end_time', title: '结束时间',width:'15%',align:'center'},
            {field: 'anchor_name', title: '主播 ',width:'15%',align:'center'},
            {field: 'live_status', title: '房间状态',templet:'#live_status',width:'10%',align:'center'},
            {field: 'right', title: '录播',align:'center',templet: '#buttonTpl',width:'15%'},
        ];
    },10,20,'LISTS');
    layList.tool(function (event,data,obj) {
        var layEvent = event;
        switch (layEvent) {
            case 'videotape':
                $eb.createModalFrame(data.name+'-录播详情',layList.Url({a:'videotape',p:{id:data.roomid}}));
                break;
            case 'edit':
                console.log(123);
                break;
            //$eb.axios.get(layList.U({a:'video',q:{uid:data.uid}})).then(function(res){
            //  })
        }
    });
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
