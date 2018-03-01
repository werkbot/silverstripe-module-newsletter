<% if $IncludeFormTag %>
  <form $AttributesHTML>
<% end_if %>

  <div class="flex-container">
    <div class="desktop-75">
      <% if $Fields %>
        <% loop $Fields %>
          $Field
        <% end_loop %>
      <% end_if %>
    </div>
    <div class="desktop-25">

      <% if $Actions %>
        <% loop $Actions %>
          $Field
        <% end_loop %>
      <% end_if %>
    </div>
  </div>

<% if $IncludeFormTag %>
  </form>
<% end_if %>
