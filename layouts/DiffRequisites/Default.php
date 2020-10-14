<style>
    /*.diff td{*/
    /*    vertical-align : top;*/
    /*    white-space    : pre;*/
    /*    white-space    : pre-wrap;*/
    /*    font-family    : monospace;*/
    /*}*/

    .diff td{
        padding:0 0.667em;
        text-align: left;
        vertical-align:top;
        white-space:pre;
        white-space:pre-wrap;
        font-family:Consolas,'Courier New',Courier,monospace;
        font-size:0.75em;
        line-height:1.333;
    }

    .diff span{
        display:block;
        min-height:1.333em;
        margin-top:-1px;
        padding:0 3px;
    }

    * html .diff span{
        height:1.333em;
    }

    .diff span:first-child{
        margin-top:0;
    }

    .diffDeleted span{
        border:1px solid rgb(255,192,192);
        background:rgb(255,224,224);
    }

    .diffInserted span{
        border:1px solid rgb(192,255,192);
        background:rgb(224,255,224);
    }

    #toStringOutput{
        margin:0 2em 2em;
    }
    .centerDiff {
        margin-left: auto;
        margin-right: auto;
    }
</style>
<div class="field buttons">
<?php if($this->isPermitted($this::AK_REQUISITES)): ?>
    <a href="index.php?view=<?php echo $this::AK_REQUISITES; ?>&inn=<?php echo $inn; ?>" target="_blank" class="button">Текущие реквизиты</a>
    <a href="index.php?view=<?php echo $this::AK_REQUISITES; ?>&inn=<?php echo $inn; ?>&uid=&date=<?php echo $date ?>" target="_blank" class="button">Реквизиты во время сдачи</a>
<?php endif; ?>
</div>
<table class="centerDiff">
    <tr>
        <td>Последние сохраненные</td>
        <td><?php echo $date; ?></td>
    </tr>
    <tr><td colspan="2"> <?php echo $diff; ?> </td></tr>
</table>
