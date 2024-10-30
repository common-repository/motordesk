
<form action="/<?php print $motordesk['url']; ?>/" method="GET">

    <div class="motordesk-search-form motordesk-select-style">
<!--
        <div>
            <label for="motordesk_search_type">Vehicle Type</label>
            <div>
                <?php print $motordesk['search']['field']['type_body']['condition']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_body">Body Type</label>
            <div>
                <?php print $motordesk['search']['field']['type_body']['result']; ?>
            </div>
        </div>
-->
        <div>
            <label for="motordesk_search_make">Manufacturer</label>
            <div>
                <?php print $motordesk['search']['field']['make_model']['condition']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_model">Model</label>
            <div>
                <?php print $motordesk['search']['field']['make_model']['result']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_vehicle_search_mileage_min" class="form-label">Mileage</label>
            <div class="motordesk-search-range">
                <?php print $motordesk['search']['field']['mileage']['min']; ?>
                to
                <?php print $motordesk['search']['field']['mileage']['max']; ?>
            </div>              
        </div>

        <div>
            <label for="motordesk_vehicle_search_price_min" class="form-label">Price</label>
            <div class="motordesk-search-range">
                <?php print $motordesk['search']['field']['price']['min']; ?>
                to
                <?php print $motordesk['search']['field']['price']['max']; ?>
            </div>              
        </div>
<!--
        <div>
            <label for="motordesk_vehicle_search_age_min" class="form-label">Age</label>
            <div class="motordesk-search-range">
                <?php print $motordesk['search']['field']['age']['min']; ?>
                to
                <?php print $motordesk['search']['field']['age']['max']; ?>
            </div>              
        </div>

        <div>
            <label for="motordesk_vehicle_search_engine_size_min" class="form-label">Engine Size</label>
            <div class="motordesk-search-range">
                <?php print $motordesk['search']['field']['engine_size']['min']; ?>
                to
                <?php print $motordesk['search']['field']['engine_size']['max']; ?>
            </div>              
        </div>
-->

        <div>
            <label for="motordesk_search_fuel">Fuel</label>
            <div>
                <?php print $motordesk['search']['field']['fuel']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_transmission">Transmission</label>
            <div>
                <?php print $motordesk['search']['field']['transmission']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_drivetrain">Drivetrain</label>
            <div>
                <?php print $motordesk['search']['field']['drivetrain']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_colour">Colour</label>
            <div>
                <?php print $motordesk['search']['field']['colour']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_seats">Seats</label>
            <div>
                <?php print $motordesk['search']['field']['seats']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_doors">Doors</label>
            <div>
                <?php print $motordesk['search']['field']['doors']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_emission_class">Emission Class</label>
            <div>
                <?php print $motordesk['search']['field']['emission_class']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_ulez">ULEZ</label>
            <div>
                <?php print $motordesk['search']['field']['ulez']; ?>
            </div>
        </div>
<!--
        <div>
            <label for="motordesk_search_caz">Clean Air Zone</label>
            <div>
                <?php print $motordesk['search']['field']['caz']; ?>
            </div>
        </div>

        <div>
            <label for="motordesk_search_model">Location</label>
            <div>
                <?php print $motordesk['search']['field']['location']; ?>
            </div>
        </div>
        <div>
            <label for="motordesk_search_group">Category</label>
            <div>
                <?php print $motordesk['search']['field']['group']; ?>
            </div>
        </div>
-->

        <div>
            <input type="submit" value="Search Vehicles" />
        </div>

    </div>

</form>
