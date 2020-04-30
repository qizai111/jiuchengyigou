{extend name="public/container"}
{block name="content"}
<div class="ibox-content order-info">

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="text-align: center">
                    录播详情
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <table>
                            <thead>
                            <tr>
                                <td style="width: 170px">录播过期时间</td>
                                <td style="width: 35%">录播创建时间</td>
                                <td style="width: 30%">录播地址</td>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            {volist name='video' id='vo' key="k" }
                            <tr>
                                <td>{$vo.expire_time}</td>
                                <td>{$vo.create_time}</td>
                                <td><a href="{$vo.media_url}" target="view_window" value="录播地址{$k}">录播地址{$k}</a></td>
                            </tr>
                            {/volist}

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
{/block}
{block name="script"}

{/block}
