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
                <div class="layui-card-header">工人列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container" id="container-action">
                        <button class="layui-btn layui-btn-sm demoReload" data-type="1" >全部</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="2">水电工</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="3">家电维修工</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="4">开锁匠</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="5">瓦工</button>
                        <button class="layui-btn layui-btn-sm demoReload" data-type="6">油漆工</button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--订单-->
                    <script type="text/html" id="order_id">
                        {{d.order_id}}<br/>
                    </script>
                    <!--用户信息-->
                    <script type="text/html" id="userinfo">
<!--                        {{d.real_name}}/-->
                        {{d.uid}}
                    </script>

                    <script type="text/html" id="status">
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
            {type:'checkbox'},
            {field: 'avatar_url', title: '头像',event:'open_image', width: '6%',align:'center', templet: '<p lay-event="open_image"><img class="avatar" style="cursor: pointer" src="{{d.avatar_url}}" ></p>'},
            {field: 'nickname', title: '姓名',width:'10%',align:'center'},
            {field: 'account', title: '手机',width:'10%',align:'center'},
            {field: 'gender', title: '性别',width:'10%',align:'center'},
            {field: 'status', title: '状态',width:'8%',align:'center'},
            {field: 'career_type', title: '工人种类',width:'8%',align:'center'},
            {field: 'is_vali', title: '认证状态',templet:'#status',width:'8%',align:'center'},
            {field: 'address', title: '地址',width:'10%',sort: true,align:'center'},
        ];
    },10,20,'LISTS');

    var action={
        // reload: function(){
        //     var demoReload = $('.demoReload');
        //     console.log(demoReload);
        //     //执行重载
        //     layList.reload('testReload', {
        //         page: {
        //             curr: 1 //重新从第 1 页开始
        //         }
        //         ,where: {
        //             key: demoReload.val()
        //         }
        //     }, 'data');
        // },
        del_order:function () {
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                var url =layList.U({c:'workers.order',a:'del_order'});
                $eb.$swal('delete',function(){
                    $eb.axios.post(url,{ids:ids}).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },{'title':'您确定要修删除订单吗？','text':'删除后将无法恢复,请谨慎操作！','confirm':'是的，我要删除'})
            }else{
                layList.msg('请选择要删除的订单');
            }
        }
    };
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
