{extend name="public/container"}
{block name="head_top"}

{/block}
{block name="content"}
<div class="layui-fluid">
    <!--搜索条件-->
    <div class="layui-row layui-col-space15" id="app" >
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                    <div class="layui-card-body">
                        <div class="layui-row layui-col-space10 layui-form-item">
                            <div class="layui-col-lg12">
                                <label class="layui-form-label">时间选择:</label>
                                <div class="layui-input-block" data-type="data" v-cloak="">
                                    <button class="layui-btn layui-btn-sm" type="button"  @click="setData(1)" >签到天数大于15天</button>
                                    <button class="layui-btn layui-btn-sm" type="button"  @click="setData(0)" >签到天数小于15天</button>
                                </div>
                            </div>
                            <div class="layui-col-lg12">
                                <label class="layui-form-label">用户昵称:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="nickname" style="width: 50%" v-model="where.nickname" placeholder="请输入姓名、UID" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-col-lg12">
                                <div class="layui-input-block">
                                    <button @click="search" type="button" class="layui-btn layui-btn-sm layui-btn-normal">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                    <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                                        <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    <!--列表-->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">分销员列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--用户信息-->
                    <script type="text/html" id="user_id">
                        {{d.user_id}}
                    </script>
                    <script type="text/html" id="certificate">
                        <text>{{d.province}}{{d.city}}{{d.district}}{{d.detail}}</text>
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
    layList.tableList('List',"{:Url('receiveList')}",function (){
        return [
            {field:'user_id',templet:'#user_id',sort: true,event:'user_id',type:'checkbox'},
            {field: 'avatar', title: '头像',width:'10%',align:'center',templet: '<p><img src="{{d.avatar}}" alt="{{d.simple_name}}" class="open_image" data-image="{{d.avatar}}"></p>'},
            {field: 'nickname', title: '用户',width:'10%',align:'center'},
            {field: 'sign_sum_day', title: '签到天数',width:'10%',align:'center'},
            {field: 'goods_number', title: '领取数量',width:'10%',align:'center'},
            {field: 'simple_name', title: '活动商品',templet:'#card_z',width:'12%',align:'center'},
            {field: 'unit', title: '单位',width:'8%',align:'center'},
            {field: 'province', title: '用户收货地址',templet:'#certificate',width:'20%',align:'center'},
            {field: 'phone', title: '联系电话',width:'10%',align:'center'},
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
