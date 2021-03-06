<div id='main-header'><h1></h1></div>
<div class="section">
  <p>MathSoc council is the governing body of the society.  The make decisions as to funding and administrational decisions.</p>

  <h3>Executive</h3>
  <p>The Executives of the society are elected by math students and entrusted to run the society on their behalf.  They are elected for either the winter and fall or summer terms of a calendar year.</p>
  <h4>Winter/Fall Executives</h4>
  <ul>
{foreach from=$EXC item=position}
    <li><a href="{$baseUrl}/positions/details?position={$position.alias}">{$position.name}</a> : {$position.desired - $position.holders} of {$position.desired} Available</li>
{foreachelse}
    <li>There are no available exec positions</li>
{/foreach}
  </ul>

  <h4>Summer Executives</h4>
  <ul>
{foreach from=$OEX item=position}
    <li><a href="{$baseUrl}/positions/details?position={$position.alias}">{$position.name}</a> :
  {foreach from=$position.holders item=holder}{$holder}{foreachelse}Position Vacant{/foreach}</li>
{foreachelse}
    <li>There are no available exec positions</li>
{/foreach}
  </ul>

  <h3>Student Representatives</h3>
  <p></p>
  <ul>
{foreach from=$REP item=position}
    <li><a href="{$baseUrl}/positions/details?position={$position.alias}">{$position.name}</a> :
  {foreach from=$position.holders item=holder}{$holder}{foreachelse}Position Vacant{/foreach}</li>
{foreachelse}
    <li>No Available student representative positions.</li>
{/foreach}
  </ul>

  <h3>Appointed Positions</h3>
  <p></p>
  <ul>
{foreach from=$APP item=position}
    <li><a href="{$baseUrl}/positions/details?position={$position.alias}">{$position.name}</a> :
  {foreach from=$position.holders item=holder}{$holder}{foreachelse}Position Vacant{/foreach}</li>
{foreachelse}
    <li>There are no available appointed positions</li>
{/foreach}
  </ul>

</div>
